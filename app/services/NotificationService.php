<?php

/**
 * Notification Service
 * Handles multi-channel notifications (Email, SMS, Push, WhatsApp)
 *
 * @author DigiParc Team
 * @version 1.0
 */
class NotificationService
{
    private $db;
    private $emailService;
    private $smsService;
    private $pushService;
    private $whatsappService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->emailService = new EmailService();
        $this->smsService = new SMSService();
        $this->pushService = new PushService();
        $this->whatsappService = new WhatsAppService();
    }

    /**
     * Send notification through multiple channels
     *
     * @param array $data Notification data
     * @return array Results per channel
     */
    public function send($data)
    {
        $results = [];

        // Get user notification preferences
        $preferences = $this->getUserPreferences($data['user_id'], $data['alert_type'] ?? 'all');

        // Check quiet hours
        if ($this->isQuietHours($preferences)) {
            return ['status' => 'skipped', 'reason' => 'quiet_hours'];
        }

        // Check priority threshold
        if (!$this->meetsPriorityThreshold($data['priority'] ?? 'medium', $preferences['priority_threshold'])) {
            return ['status' => 'skipped', 'reason' => 'priority_threshold'];
        }

        // Send via enabled channels
        if ($preferences['channel_email']) {
            $results['email'] = $this->sendEmail($data);
        }

        if ($preferences['channel_sms']) {
            $results['sms'] = $this->sendSMS($data);
        }

        if ($preferences['channel_push']) {
            $results['push'] = $this->sendPush($data);
        }

        if ($preferences['channel_whatsapp']) {
            $results['whatsapp'] = $this->sendWhatsApp($data);
        }

        // Always create internal notification
        if ($preferences['channel_internal']) {
            $results['internal'] = $this->sendInternal($data);
        }

        return $results;
    }

    /**
     * Send email notification
     */
    private function sendEmail($data)
    {
        try {
            $user = $this->getUser($data['user_id']);

            if (!$user || !$user['email']) {
                return $this->logNotification($data, 'email', $user['email'] ?? '', 'failed', 'No email address');
            }

            $emailData = [
                'to' => $user['email'],
                'subject' => $data['subject'] ?? $data['title'],
                'body' => $this->formatEmailBody($data),
                'company_id' => $data['company_id']
            ];

            $result = $this->emailService->send($emailData);

            return $this->logNotification(
                $data,
                'email',
                $user['email'],
                $result ? 'sent' : 'failed',
                $result ? null : 'Email service error'
            );

        } catch (Exception $e) {
            return $this->logNotification($data, 'email', $user['email'] ?? '', 'failed', $e->getMessage());
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSMS($data)
    {
        try {
            $user = $this->getUser($data['user_id']);

            if (!$user || !$user['phone']) {
                return $this->logNotification($data, 'sms', $user['phone'] ?? '', 'failed', 'No phone number');
            }

            $message = $this->formatSMSMessage($data);

            $result = $this->smsService->send([
                'to' => $user['phone'],
                'message' => $message
            ]);

            return $this->logNotification(
                $data,
                'sms',
                $user['phone'],
                $result['status'] ?? 'failed',
                $result['error'] ?? null,
                $result['metadata'] ?? null
            );

        } catch (Exception $e) {
            return $this->logNotification($data, 'sms', $user['phone'] ?? '', 'failed', $e->getMessage());
        }
    }

    /**
     * Send push notification
     */
    private function sendPush($data)
    {
        try {
            $user = $this->getUser($data['user_id']);

            if (!$user) {
                return $this->logNotification($data, 'push', 'user_' . $data['user_id'], 'failed', 'User not found');
            }

            $pushData = [
                'user_id' => $user['id'],
                'title' => $data['title'],
                'body' => $data['message'],
                'icon' => $this->getPriorityIcon($data['priority'] ?? 'medium'),
                'url' => $data['action_url'] ?? '/alerts',
                'data' => $data['metadata'] ?? []
            ];

            $result = $this->pushService->send($pushData);

            return $this->logNotification(
                $data,
                'push',
                'user_' . $user['id'],
                $result['status'] ?? 'failed',
                $result['error'] ?? null,
                $result['metadata'] ?? null
            );

        } catch (Exception $e) {
            return $this->logNotification($data, 'push', 'user_' . $data['user_id'], 'failed', $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification
     */
    private function sendWhatsApp($data)
    {
        try {
            $user = $this->getUser($data['user_id']);

            if (!$user || !$user['phone']) {
                return $this->logNotification($data, 'whatsapp', $user['phone'] ?? '', 'failed', 'No phone number');
            }

            $message = $this->formatWhatsAppMessage($data);

            $result = $this->whatsappService->send([
                'to' => $user['phone'],
                'message' => $message
            ]);

            return $this->logNotification(
                $data,
                'whatsapp',
                $user['phone'],
                $result['status'] ?? 'failed',
                $result['error'] ?? null,
                $result['metadata'] ?? null
            );

        } catch (Exception $e) {
            return $this->logNotification($data, 'whatsapp', $user['phone'] ?? '', 'failed', $e->getMessage());
        }
    }

    /**
     * Create internal notification
     */
    private function sendInternal($data)
    {
        // This is just logging - actual display happens via alerts table
        return $this->logNotification(
            $data,
            'internal',
            'user_' . $data['user_id'],
            'delivered'
        );
    }

    /**
     * Get user notification preferences
     */
    private function getUserPreferences($userId, $alertType = 'all')
    {
        $sql = "SELECT * FROM notification_preferences
                WHERE user_id = :user_id
                AND (alert_type = :alert_type OR alert_type = 'all')
                AND enabled = 1
                ORDER BY FIELD(alert_type, :alert_type, 'all')
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':alert_type', $alertType);
        $stmt->execute();

        $prefs = $stmt->fetch(PDO::FETCH_ASSOC);

        // Default preferences if none found
        if (!$prefs) {
            return [
                'channel_email' => true,
                'channel_sms' => false,
                'channel_push' => true,
                'channel_whatsapp' => false,
                'channel_internal' => true,
                'priority_threshold' => 'medium',
                'quiet_hours_start' => null,
                'quiet_hours_end' => null
            ];
        }

        return $prefs;
    }

    /**
     * Check if current time is within quiet hours
     */
    private function isQuietHours($preferences)
    {
        if (!$preferences['quiet_hours_start'] || !$preferences['quiet_hours_end']) {
            return false;
        }

        $now = date('H:i:s');
        $start = $preferences['quiet_hours_start'];
        $end = $preferences['quiet_hours_end'];

        // Handle overnight quiet hours (e.g., 22:00 to 08:00)
        if ($start > $end) {
            return $now >= $start || $now <= $end;
        }

        return $now >= $start && $now <= $end;
    }

    /**
     * Check if notification priority meets threshold
     */
    private function meetsPriorityThreshold($priority, $threshold)
    {
        $priorities = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];

        return ($priorities[$priority] ?? 2) >= ($priorities[$threshold] ?? 2);
    }

    /**
     * Log notification
     */
    private function logNotification($data, $channel, $recipient, $status, $error = null, $metadata = null)
    {
        $sql = "INSERT INTO notification_logs (
                    company_id, user_id, alert_id, channel, recipient,
                    subject, message, status, error_message, metadata, sent_at
                ) VALUES (
                    :company_id, :user_id, :alert_id, :channel, :recipient,
                    :subject, :message, :status, :error_message, :metadata, :sent_at
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $data['company_id']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':alert_id', $data['alert_id'] ?? null);
        $stmt->bindParam(':channel', $channel);
        $stmt->bindParam(':recipient', $recipient);
        $stmt->bindParam(':subject', $data['subject'] ?? $data['title']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':error_message', $error);

        $metadataJson = $metadata ? json_encode($metadata) : null;
        $stmt->bindParam(':metadata', $metadataJson);

        $sentAt = ($status === 'sent' || $status === 'delivered') ? date('Y-m-d H:i:s') : null;
        $stmt->bindParam(':sent_at', $sentAt);

        $stmt->execute();

        return [
            'channel' => $channel,
            'status' => $status,
            'recipient' => $recipient,
            'error' => $error,
            'log_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Get user data
     */
    private function getUser($userId)
    {
        $sql = "SELECT id, email, phone, first_name, last_name
                FROM users
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Format email body
     */
    private function formatEmailBody($data)
    {
        $company = getCurrentCompany();

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .alert-box { padding: 15px; margin: 20px 0; border-left: 4px solid; }
        .alert-critical { border-left-color: #dc3545; background: #f8d7da; }
        .alert-high { border-left-color: #fd7e14; background: #fff3cd; }
        .alert-medium { border-left-color: #0dcaf0; background: #d1ecf1; }
        .alert-low { border-left-color: #6c757d; background: #e2e3e5; }
        .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
        .button { display: inline-block; padding: 10px 20px; background: #0d6efd; color: white !important; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>DigiParc - Alerte</h1>
        </div>
        <div class="content">
            <div class="alert-box alert-' . ($data['priority'] ?? 'medium') . '">
                <h2>' . htmlspecialchars($data['title']) . '</h2>
                <p>' . nl2br(htmlspecialchars($data['message'])) . '</p>
            </div>';

        if (!empty($data['action_url'])) {
            $html .= '<p style="text-align: center;">
                <a href="' . APP_URL . $data['action_url'] . '" class="button">Voir les détails</a>
            </p>';
        }

        $html .= '
        </div>
        <div class="footer">
            <p>Vous recevez cet email car vous êtes abonné aux alertes DigiParc.<br>
            <a href="' . APP_URL . '/settings/notifications">Gérer mes préférences</a></p>
            <p>&copy; ' . date('Y') . ' ' . htmlspecialchars($company['name'] ?? 'DigiParc') . '</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Format SMS message (max 160 chars)
     */
    private function formatSMSMessage($data)
    {
        $prefix = '';
        switch ($data['priority'] ?? 'medium') {
            case 'critical':
                $prefix = '🔴 URGENT: ';
                break;
            case 'high':
                $prefix = '⚠️ ';
                break;
        }

        $message = $prefix . $data['title'];

        // Keep it short for SMS
        if (strlen($message) > 160) {
            $message = substr($message, 0, 157) . '...';
        }

        return $message;
    }

    /**
     * Format WhatsApp message
     */
    private function formatWhatsAppMessage($data)
    {
        $icon = $this->getPriorityIcon($data['priority'] ?? 'medium');

        $message = "*{$icon} DigiParc Alert*\n\n";
        $message .= "*" . $data['title'] . "*\n\n";
        $message .= $data['message'];

        if (!empty($data['action_url'])) {
            $message .= "\n\n🔗 " . APP_URL . $data['action_url'];
        }

        return $message;
    }

    /**
     * Get priority icon
     */
    private function getPriorityIcon($priority)
    {
        $icons = [
            'critical' => '🔴',
            'high' => '🟠',
            'medium' => '🔵',
            'low' => '⚪'
        ];

        return $icons[$priority] ?? '🔵';
    }

    /**
     * Get notification statistics
     */
    public function getStatistics($companyId, $period = 'month')
    {
        $dateFilter = match($period) {
            'day' => 'DATE(NOW())',
            'week' => 'DATE_SUB(NOW(), INTERVAL 7 DAY)',
            'month' => 'DATE_SUB(NOW(), INTERVAL 30 DAY)',
            default => 'DATE_SUB(NOW(), INTERVAL 30 DAY)'
        };

        $sql = "SELECT
                    channel,
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'sent' OR status = 'delivered' THEN 1 END) as success,
                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
                    COUNT(CASE WHEN opened_at IS NOT NULL THEN 1 END) as opened,
                    COUNT(CASE WHEN clicked_at IS NOT NULL THEN 1 END) as clicked
                FROM notification_logs
                WHERE company_id = :company_id
                AND created_at >= $dateFilter
                GROUP BY channel";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
