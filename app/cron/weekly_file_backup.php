<?php

/**
 * CRON Job: Weekly File Backup
 * Run weekly (Sunday 4 AM) to backup uploaded files
 *
 * Usage: php /path/to/app/cron/weekly_file_backup.php
 * Or add to crontab: 0 4 * * 0 php /path/to/app/cron/weekly_file_backup.php
 *
 * @author DigiParc Team
 * @version 1.0
 */

// Load application bootstrap
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../services/BackupService.php';

echo "=== DigiParc Weekly File Backup ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $backupService = new BackupService();

    // Create files backup
    echo "Creating files backup...\n";
    $result = $backupService->backupFiles('weekly');

    if ($result['success']) {
        echo "✓ Files backup created: {$result['file']}\n";
        echo "  Size: " . round($result['size'] / 1024 / 1024, 2) . " MB\n";
    } else {
        echo "✗ Files backup failed: {$result['error']}\n";
        exit(1);
    }

    echo "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";
    exit(0);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
