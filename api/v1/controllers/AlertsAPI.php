<?php

/**
 * Alerts API Controller
 * Handles alert operations via API
 *
 * @author Pakiparc Team
 * @version 1.0
 */

require_once __DIR__ . '/../../models/Alert.php';

class AlertsAPI
{
    private $alertModel;

    public function __construct()
    {
        $this->alertModel = new Alert();
    }

    public function index($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $status = $_GET['status'] ?? null;
            $priority = $_GET['priority'] ?? null;

            $filters = [];
            if ($status) $filters['status'] = $status;
            if ($priority) $filters['priority'] = $priority;

            $result = $this->alertModel->getAlerts($companyId, $page, $limit, $filters);

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'data' => $result['alerts'],
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($result['total'] / $limit)
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function show($params)
    {
        try {
            $id = $params['id'];
            $companyId = $_SESSION['company_id'];

            $alert = $this->alertModel->getAlert($id, $companyId);

            if (!$alert) {
                http_response_code(404);
                echo json_encode(['error' => true, 'message' => 'Alert not found']);
                return;
            }

            // Mark as read
            $this->alertModel->markAsRead($id, $companyId);

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $alert]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function acknowledge($params)
    {
        try {
            $id = $params['id'];
            $companyId = $_SESSION['company_id'];
            $userId = $_SESSION['user_id'];

            $result = $this->alertModel->acknowledgeAlert($id, $companyId, $userId);

            if (!$result) {
                http_response_code(404);
                echo json_encode(['error' => true, 'message' => 'Alert not found']);
                return;
            }

            http_response_code(200);
            echo json_encode(['error' => false, 'message' => 'Alert acknowledged successfully']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }
}
