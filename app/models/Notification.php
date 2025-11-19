<?php

/**
 * Notification Model
 * Handles notification preferences and history
 *
 * @author DigiParc Team
 * @version 1.0
 */

class Notification
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get user notification preferences
     */
    public function getUserPreferences($userId, $alertType = 'all')
    {
        $sql = "SELECT * FROM alert_notification_preferences
                WHERE user_id = :user_id AND alert_type = :alert_type
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':alert_type', $alertType);
        $stmt->execute();

        $prefs = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return default preferences if none found
        if (!$prefs) {
            return [
                'channel_email' => true,
                'channel_sms' => false,
                'channel_push' => true,
                'channel_whatsapp' => false,
                'priority_threshold' => 'medium',
                'quiet_hours_start' => null,
                'quiet_hours_end' => null
            ];
        }

        return $prefs;
    }

    /**
     * Update user notification preferences
     */
    public function updateUserPreferences($userId, $alertType, $preferences)
    {
        // Check if preferences exist
        $checkSql = "SELECT id FROM alert_notification_preferences
                     WHERE user_id = :user_id AND alert_type = :alert_type
                     LIMIT 1";

        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->bindParam(':alert_type', $alertType);
        $checkStmt->execute();

        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            // Update existing preferences
            $sql = "UPDATE alert_notification_preferences SET
                    channel_email = :channel_email,
                    channel_sms = :channel_sms,
                    channel_push = :channel_push,
                    channel_whatsapp = :channel_whatsapp,
                    priority_threshold = :priority_threshold,
                    quiet_hours_start = :quiet_hours_start,
                    quiet_hours_end = :quiet_hours_end,
                    updated_at = NOW()
                    WHERE user_id = :user_id AND alert_type = :alert_type";
        } else {
            // Insert new preferences
            $sql = "INSERT INTO alert_notification_preferences
                    (user_id, alert_type, channel_email, channel_sms, channel_push, channel_whatsapp,
                     priority_threshold, quiet_hours_start, quiet_hours_end, created_at)
                    VALUES
                    (:user_id, :alert_type, :channel_email, :channel_sms, :channel_push, :channel_whatsapp,
                     :priority_threshold, :quiet_hours_start, :quiet_hours_end, NOW())";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':alert_type', $alertType);
        $stmt->bindParam(':channel_email', $preferences['channel_email']);
        $stmt->bindParam(':channel_sms', $preferences['channel_sms']);
        $stmt->bindParam(':channel_push', $preferences['channel_push']);
        $stmt->bindParam(':channel_whatsapp', $preferences['channel_whatsapp']);
        $stmt->bindParam(':priority_threshold', $preferences['priority_threshold']);
        $stmt->bindParam(':quiet_hours_start', $preferences['quiet_hours_start']);
        $stmt->bindParam(':quiet_hours_end', $preferences['quiet_hours_end']);

        return $stmt->execute();
    }

    /**
     * Log notification
     */
    public function logNotification($userId, $alertId, $channel, $status, $details = null)
    {
        $sql = "INSERT INTO alert_notification_logs
                (user_id, alert_id, channel, status, details, sent_at)
                VALUES (:user_id, :alert_id, :channel, :status, :details, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':alert_id', $alertId);
        $stmt->bindParam(':channel', $channel);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':details', $details);

        return $stmt->execute();
    }

    /**
     * Get notification history
     */
    public function getNotificationHistory($userId, $page = 1, $limit = 50)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT nl.*, a.title as alert_title, a.type as alert_type
                FROM alert_notification_logs nl
                LEFT JOIN alerts a ON nl.alert_id = a.id
                WHERE nl.user_id = :user_id
                ORDER BY nl.sent_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get notification stats
     */
    public function getNotificationStats($userId, $period = 'month')
    {
        $dateFilter = $this->getPeriodFilter($period);

        $sql = "SELECT
                    channel,
                    status,
                    COUNT(*) as count
                FROM alert_notification_logs
                WHERE user_id = :user_id
                AND $dateFilter
                GROUP BY channel, status";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get period filter for SQL
     */
    private function getPeriodFilter($period)
    {
        switch ($period) {
            case 'today':
                return "DATE(sent_at) = CURDATE()";
            case 'week':
                return "sent_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            case 'month':
                return "sent_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            case 'year':
                return "sent_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "sent_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        }
    }
}
