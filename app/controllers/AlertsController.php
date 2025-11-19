<?php

/**
 * Alerts Controller
 * Handles alert management interface
 *
 * @author Pakiparc Team
 * @version 1.0
 */

require_once __DIR__ . '/../models/Alert.php';

class AlertsController
{
    private $alertModel;

    public function __construct()
    {
        $this->alertModel = new Alert();
    }

    /**
     * Display alerts list
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $companyId = $_SESSION['company_id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $status = $_GET['status'] ?? null;
        $priority = $_GET['priority'] ?? null;

        $filters = [];
        if ($status) $filters['status'] = $status;
        if ($priority) $filters['priority'] = $priority;

        $result = $this->alertModel->getAlerts($companyId, $page, 20, $filters);

        $data = [
            'alerts' => $result['alerts'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => ceil($result['total'] / 20),
            'status_filter' => $status,
            'priority_filter' => $priority
        ];

        require_once __DIR__ . '/../views/alerts/index.php';
    }

    /**
     * Display single alert
     */
    public function view()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /alerts');
            exit;
        }

        $companyId = $_SESSION['company_id'];
        $alert = $this->alertModel->getAlert($id, $companyId);

        if (!$alert) {
            $_SESSION['error'] = 'Alert not found';
            header('Location: /alerts');
            exit;
        }

        // Mark as read
        $this->alertModel->markAsRead($id, $companyId);

        $data = ['alert' => $alert];

        require_once __DIR__ . '/../views/alerts/view.php';
    }

    /**
     * Acknowledge alert
     */
    public function acknowledge()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /alerts');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: /alerts');
            exit;
        }

        $companyId = $_SESSION['company_id'];
        $userId = $_SESSION['user_id'];

        $result = $this->alertModel->acknowledgeAlert($id, $companyId, $userId);

        if ($result) {
            $_SESSION['success'] = 'Alert acknowledged successfully';
        } else {
            $_SESSION['error'] = 'Failed to acknowledge alert';
        }

        header('Location: /alerts/view?id=' . $id);
        exit;
    }

    /**
     * Archive alert
     */
    public function archive()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /alerts');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: /alerts');
            exit;
        }

        $companyId = $_SESSION['company_id'];

        $result = $this->alertModel->archiveAlert($id, $companyId);

        if ($result) {
            $_SESSION['success'] = 'Alert archived successfully';
        } else {
            $_SESSION['error'] = 'Failed to archive alert';
        }

        header('Location: /alerts');
        exit;
    }

    /**
     * Alert settings
     */
    public function settings()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveSettings();
            return;
        }

        $companyId = $_SESSION['company_id'];
        $rules = $this->alertModel->getAlertRules($companyId);

        $data = ['rules' => $rules];

        require_once __DIR__ . '/../views/alerts/settings.php';
    }

    /**
     * Save alert settings
     */
    private function saveSettings()
    {
        $companyId = $_SESSION['company_id'];
        $rules = $_POST['rules'] ?? [];

        foreach ($rules as $type => $config) {
            $this->alertModel->updateAlertRule($companyId, $type, $config);
        }

        $_SESSION['success'] = 'Alert settings saved successfully';
        header('Location: /alerts/settings');
        exit;
    }
}
