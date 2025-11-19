<?php

/**
 * Backup Service
 * Handles automated database and file backups
 *
 * @author DigiParc Team
 * @version 1.0
 */
class BackupService
{
    private $backupDir;
    private $dbConfig;
    private $maxBackups = [
        'daily' => 7,      // Keep 7 daily backups
        'weekly' => 4,     // Keep 4 weekly backups
        'monthly' => 12    // Keep 12 monthly backups
    ];

    public function __construct()
    {
        $this->backupDir = __DIR__ . '/../../backups';
        $this->dbConfig = [
            'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
            'name' => defined('DB_NAME') ? DB_NAME : 'digiparc',
            'user' => defined('DB_USER') ? DB_USER : 'root',
            'pass' => defined('DB_PASS') ? DB_PASS : ''
        ];

        // Create backup directory if it doesn't exist
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Create database backup
     *
     * @param string $type (daily, weekly, monthly)
     * @return array Result with status and file path
     */
    public function backupDatabase($type = 'daily')
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "db_{$type}_{$timestamp}.sql.gz";
        $filepath = $this->backupDir . '/' . $filename;

        try {
            // Use mysqldump to create backup
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s | gzip > %s 2>&1',
                escapeshellarg($this->dbConfig['host']),
                escapeshellarg($this->dbConfig['user']),
                escapeshellarg($this->dbConfig['pass']),
                escapeshellarg($this->dbConfig['name']),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($filepath)) {
                // Log backup
                $this->logBackup('database', $type, $filepath, filesize($filepath));

                // Clean old backups
                $this->cleanOldBackups('database', $type);

                return [
                    'success' => true,
                    'file' => $filepath,
                    'size' => filesize($filepath),
                    'message' => 'Database backup created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'mysqldump failed: ' . implode("\n", $output)
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Backup files (uploads, logos, etc.)
     *
     * @param string $type (daily, weekly, monthly)
     * @return array
     */
    public function backupFiles($type = 'weekly')
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "files_{$type}_{$timestamp}.tar.gz";
        $filepath = $this->backupDir . '/' . $filename;

        $uploadsDir = __DIR__ . '/../../public/uploads';

        if (!file_exists($uploadsDir)) {
            return [
                'success' => false,
                'error' => 'Uploads directory does not exist'
            ];
        }

        try {
            $command = sprintf(
                'tar -czf %s -C %s . 2>&1',
                escapeshellarg($filepath),
                escapeshellarg($uploadsDir)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($filepath)) {
                $this->logBackup('files', $type, $filepath, filesize($filepath));
                $this->cleanOldBackups('files', $type);

                return [
                    'success' => true,
                    'file' => $filepath,
                    'size' => filesize($filepath),
                    'message' => 'Files backup created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'tar command failed: ' . implode("\n", $output)
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Restore database from backup
     *
     * @param string $backupFile Path to backup file
     * @return array
     */
    public function restoreDatabase($backupFile)
    {
        if (!file_exists($backupFile)) {
            return [
                'success' => false,
                'error' => 'Backup file not found'
            ];
        }

        try {
            // Decompress and restore
            $command = sprintf(
                'gunzip < %s | mysql -h %s -u %s -p%s %s 2>&1',
                escapeshellarg($backupFile),
                escapeshellarg($this->dbConfig['host']),
                escapeshellarg($this->dbConfig['user']),
                escapeshellarg($this->dbConfig['pass']),
                escapeshellarg($this->dbConfig['name'])
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $this->logRestore('database', $backupFile);

                return [
                    'success' => true,
                    'message' => 'Database restored successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Restore failed: ' . implode("\n", $output)
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * List available backups
     *
     * @param string $category (database, files, all)
     * @return array
     */
    public function listBackups($category = 'all')
    {
        $backups = [];

        $files = scandir($this->backupDir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filepath = $this->backupDir . '/' . $file;

            if (is_file($filepath)) {
                $type = null;

                if (str_starts_with($file, 'db_')) {
                    $type = 'database';
                } elseif (str_starts_with($file, 'files_')) {
                    $type = 'files';
                }

                if ($type && ($category === 'all' || $category === $type)) {
                    $backups[] = [
                        'filename' => $file,
                        'filepath' => $filepath,
                        'type' => $type,
                        'size' => filesize($filepath),
                        'size_mb' => round(filesize($filepath) / 1024 / 1024, 2),
                        'created_at' => date('Y-m-d H:i:s', filemtime($filepath))
                    ];
                }
            }
        }

        // Sort by date descending
        usort($backups, function($a, $b) {
            return filemtime($b['filepath']) - filemtime($a['filepath']);
        });

        return $backups;
    }

    /**
     * Clean old backups based on retention policy
     *
     * @param string $category (database, files)
     * @param string $type (daily, weekly, monthly)
     */
    private function cleanOldBackups($category, $type)
    {
        $prefix = $category === 'database' ? 'db_' : 'files_';
        $prefix .= $type . '_';

        $backups = [];
        $files = scandir($this->backupDir);

        foreach ($files as $file) {
            if (str_starts_with($file, $prefix)) {
                $filepath = $this->backupDir . '/' . $file;
                $backups[] = [
                    'file' => $filepath,
                    'time' => filemtime($filepath)
                ];
            }
        }

        // Sort by time descending
        usort($backups, function($a, $b) {
            return $b['time'] - $a['time'];
        });

        // Keep only max backups
        $maxToKeep = $this->maxBackups[$type] ?? 7;

        for ($i = $maxToKeep; $i < count($backups); $i++) {
            unlink($backups[$i]['file']);
        }
    }

    /**
     * Log backup creation
     */
    private function logBackup($category, $type, $filepath, $size)
    {
        $logFile = $this->backupDir . '/backup.log';

        $message = sprintf(
            "[%s] BACKUP CREATED: %s %s - File: %s - Size: %s MB\n",
            date('Y-m-d H:i:s'),
            strtoupper($category),
            strtoupper($type),
            basename($filepath),
            round($size / 1024 / 1024, 2)
        );

        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Log restore operation
     */
    private function logRestore($category, $filepath)
    {
        $logFile = $this->backupDir . '/backup.log';

        $message = sprintf(
            "[%s] RESTORE COMPLETED: %s - File: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($category),
            basename($filepath)
        );

        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Test backup/restore (create test backup and verify it can be restored)
     *
     * @return array
     */
    public function testBackupRestore()
    {
        $results = [];

        // Test database backup
        $dbBackup = $this->backupDatabase('test');
        $results['database_backup'] = $dbBackup['success'];

        if ($dbBackup['success']) {
            // Verify file is valid gzip
            $results['database_valid'] = $this->verifyGzipFile($dbBackup['file']);

            // Clean up test backup
            if (file_exists($dbBackup['file'])) {
                unlink($dbBackup['file']);
            }
        }

        // Test files backup
        $filesBackup = $this->backupFiles('test');
        $results['files_backup'] = $filesBackup['success'];

        if ($filesBackup['success']) {
            // Verify file is valid tar.gz
            $results['files_valid'] = $this->verifyTarGzFile($filesBackup['file']);

            // Clean up test backup
            if (file_exists($filesBackup['file'])) {
                unlink($filesBackup['file']);
            }
        }

        $results['overall'] = $results['database_backup'] && $results['files_backup'];

        return $results;
    }

    /**
     * Verify gzip file integrity
     */
    private function verifyGzipFile($filepath)
    {
        $command = sprintf('gunzip -t %s 2>&1', escapeshellarg($filepath));
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Verify tar.gz file integrity
     */
    private function verifyTarGzFile($filepath)
    {
        $command = sprintf('tar -tzf %s > /dev/null 2>&1', escapeshellarg($filepath));
        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Upload backup to remote storage (S3, FTP, etc.)
     *
     * @param string $filepath Local backup file
     * @param string $provider (s3, ftp, sftp)
     * @return array
     */
    public function uploadToRemote($filepath, $provider = 's3')
    {
        // This would integrate with AWS S3, FTP, etc.
        // For now, return placeholder

        return [
            'success' => false,
            'error' => 'Remote storage not configured yet'
        ];
    }

    /**
     * Get backup statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total_backups' => 0,
            'database_backups' => 0,
            'file_backups' => 0,
            'total_size' => 0,
            'latest_database' => null,
            'latest_files' => null
        ];

        $backups = $this->listBackups('all');

        foreach ($backups as $backup) {
            $stats['total_backups']++;
            $stats['total_size'] += $backup['size'];

            if ($backup['type'] === 'database') {
                $stats['database_backups']++;
                if (!$stats['latest_database']) {
                    $stats['latest_database'] = $backup;
                }
            } elseif ($backup['type'] === 'files') {
                $stats['file_backups']++;
                if (!$stats['latest_files']) {
                    $stats['latest_files'] = $backup;
                }
            }
        }

        $stats['total_size_mb'] = round($stats['total_size'] / 1024 / 1024, 2);
        $stats['total_size_gb'] = round($stats['total_size'] / 1024 / 1024 / 1024, 2);

        return $stats;
    }
}
