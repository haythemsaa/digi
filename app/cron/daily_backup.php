<?php

/**
 * CRON Job: Daily Database Backup
 * Run daily at 3 AM to backup database
 *
 * Usage: php /path/to/app/cron/daily_backup.php
 * Or add to crontab: 0 3 * * * php /path/to/app/cron/daily_backup.php
 *
 * @author Pakiparc Team
 * @version 1.0
 */

// Load application bootstrap
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../services/BackupService.php';

echo "=== Pakiparc Daily Backup ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $backupService = new BackupService();

    // Create database backup
    echo "Creating database backup...\n";
    $dbResult = $backupService->backupDatabase('daily');

    if ($dbResult['success']) {
        echo "✓ Database backup created: {$dbResult['file']}\n";
        echo "  Size: " . round($dbResult['size'] / 1024 / 1024, 2) . " MB\n";
    } else {
        echo "✗ Database backup failed: {$dbResult['error']}\n";
        exit(1);
    }

    // On Sunday, also create weekly backup
    if (date('w') == 0) {
        echo "\nCreating weekly backup (Sunday)...\n";
        $weeklyResult = $backupService->backupDatabase('weekly');

        if ($weeklyResult['success']) {
            echo "✓ Weekly backup created\n";
        }
    }

    // On 1st of month, create monthly backup
    if (date('j') == 1) {
        echo "\nCreating monthly backup (1st of month)...\n";
        $monthlyResult = $backupService->backupDatabase('monthly');

        if ($monthlyResult['success']) {
            echo "✓ Monthly backup created\n";
        }
    }

    // Show statistics
    echo "\n=== Backup Statistics ===\n";
    $stats = $backupService->getStatistics();
    echo "Total backups: {$stats['total_backups']}\n";
    echo "Total size: {$stats['total_size_mb']} MB ({$stats['total_size_gb']} GB)\n";
    echo "Database backups: {$stats['database_backups']}\n";
    echo "File backups: {$stats['file_backups']}\n";

    if ($stats['latest_database']) {
        echo "Latest database backup: {$stats['latest_database']['created_at']}\n";
    }

    echo "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";
    exit(0);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
