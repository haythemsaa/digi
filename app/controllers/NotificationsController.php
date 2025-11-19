<?php

/**
 * Notifications Controller
 * Handles notification preferences and history
 *
 * @author DigiParc Team
 * @version 1.0
 */

require_once __DIR__ . '/../models/Notification.php';

class NotificationsController
{
    private $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    /**
     * Display notification preferences
     */
    public function preferences()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->savePreferences();
            return;
        }

        $userId = $_SESSION['user_id'];
        $preferences = $this->notificationModel->getUserPreferences($userId, 'all');

        $data = ['preferences' => $preferences];

        require_once __DIR__ . '/../views/notifications/preferences.php';
    }

    /**
     * Save notification preferences
     */
    private function savePreferences()
    {
        $userId = $_SESSION['user_id'];

        $preferences = [
            'channel_email' => isset($_POST['channel_email']) ? 1 : 0,
            'channel_sms' => isset($_POST['channel_sms']) ? 1 : 0,
            'channel_push' => isset($_POST['channel_push']) ? 1 : 0,
            'channel_whatsapp' => isset($_POST['channel_whatsapp']) ? 1 : 0,
            'priority_threshold' => $_POST['priority_threshold'] ?? 'medium',
            'quiet_hours_start' => $_POST['quiet_hours_start'] ?? null,
            'quiet_hours_end' => $_POST['quiet_hours_end'] ?? null
        ];

        $this->notificationModel->updateUserPreferences($userId, 'all', $preferences);

        $_SESSION['success'] = 'Notification preferences saved successfully';
        header('Location: /notifications/preferences');
        exit;
    }

    /**
     * Display notification history
     */
    public function history()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $notifications = $this->notificationModel->getNotificationHistory($userId, $page, 50);

        $data = [
            'notifications' => $notifications,
            'page' => $page
        ];

        require_once __DIR__ . '/../views/notifications/history.php';
    }

    /**
     * Display notification stats
     */
    public function stats()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $period = $_GET['period'] ?? 'month';

        $stats = $this->notificationModel->getNotificationStats($userId, $period);

        $data = [
            'stats' => $stats,
            'period' => $period
        ];

        require_once __DIR__ . '/../views/notifications/stats.php';
    }
}
