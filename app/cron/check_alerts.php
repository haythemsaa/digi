<?php

/**
 * CRON Job: Check and Generate Alerts
 * Run every hour to check for conditions that trigger alerts
 *
 * Usage: php /path/to/app/cron/check_alerts.php
 * Or add to crontab: 0 * * * * php /path/to/app/cron/check_alerts.php
 *
 * @author DigiParc Team
 * @version 1.0
 */

// Load application bootstrap
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Alert.php';
require_once __DIR__ . '/../services/NotificationService.php';

echo "=== DigiParc Alert Checker ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $alertModel = new Alert();
    $notificationService = new NotificationService();

    // Get all active companies
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, name FROM companies WHERE status = 'active'");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalAlerts = 0;
    $totalNotifications = 0;

    foreach ($companies as $company) {
        echo "Checking company: {$company['name']} (ID: {$company['id']})\n";

        // Check and generate alerts for this company
        $generatedAlerts = $alertModel->checkAlertRules($company['id']);

        if (!empty($generatedAlerts)) {
            echo "  ✓ Generated " . count($generatedAlerts) . " new alerts\n";
            $totalAlerts += count($generatedAlerts);

            // Send notifications for new alerts
            foreach ($generatedAlerts as $alertId) {
                // Get alert details
                $alert = $alertModel->getById($alertId, $company['id']);

                if ($alert) {
                    // Get company admins to notify
                    $stmt = $db->prepare("
                        SELECT id FROM users
                        WHERE company_id = :company_id
                        AND role IN ('admin', 'manager')
                        AND status = 'active'
                    ");
                    $stmt->bindParam(':company_id', $company['id']);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($users as $user) {
                        $notificationData = [
                            'company_id' => $company['id'],
                            'user_id' => $user['id'],
                            'alert_id' => $alertId,
                            'alert_type' => $alert['type'],
                            'priority' => $alert['priority'],
                            'title' => $alert['title'],
                            'message' => $alert['message'],
                            'action_url' => $alert['action_url'],
                            'metadata' => json_decode($alert['metadata'], true)
                        ];

                        $results = $notificationService->send($notificationData);
                        $totalNotifications++;

                        echo "  📧 Sent notifications to user {$user['id']}: ";
                        foreach ($results as $channel => $result) {
                            if ($result['status'] === 'sent' || $result['status'] === 'delivered') {
                                echo "$channel✓ ";
                            }
                        }
                        echo "\n";
                    }
                }
            }
        } else {
            echo "  ℹ No new alerts\n";
        }
    }

    // Archive expired alerts
    echo "\nArchiving expired alerts...\n";
    $alertModel->archiveExpiredAlerts();

    echo "\n=== Summary ===\n";
    echo "Total alerts generated: $totalAlerts\n";
    echo "Total notifications sent: $totalNotifications\n";
    echo "Completed at: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
