<?php

/**
 * Missions API Controller
 * Handles mission CRUD operations via API
 *
 * @author Pakiparc Team
 * @version 1.0
 */

class MissionsAPI
{
    public function index($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;

            $db = Database::getInstance()->getConnection();

            $countSql = "SELECT COUNT(*) as total FROM missions WHERE company_id = :company_id";
            $countStmt = $db->prepare($countSql);
            $countStmt->bindParam(':company_id', $companyId);
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $sql = "SELECT m.*, v.registration as vehicle_registration,
                           CONCAT(d.first_name, ' ', d.last_name) as driver_name
                    FROM missions m
                    LEFT JOIN vehicles v ON m.vehicle_id = v.id
                    LEFT JOIN drivers d ON m.driver_id = d.id
                    WHERE m.company_id = :company_id
                    ORDER BY m.start_date DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'data' => $missions,
                'pagination' => ['total' => $total, 'page' => $page, 'limit' => $limit, 'pages' => ceil($total / $limit)]
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
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT m.*, v.registration as vehicle_registration,
                           CONCAT(d.first_name, ' ', d.last_name) as driver_name
                    FROM missions m
                    LEFT JOIN vehicles v ON m.vehicle_id = v.id
                    LEFT JOIN drivers d ON m.driver_id = d.id
                    WHERE m.id = :id AND m.company_id = :company_id
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            $mission = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$mission) {
                http_response_code(404);
                echo json_encode(['error' => true, 'message' => 'Mission not found']);
                return;
            }

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $mission]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function create($params = [])
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['vehicle_id']) || !isset($input['driver_id']) || !isset($input['start_date'])) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Vehicle ID, driver ID, and start date are required']);
            return;
        }

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $sql = "INSERT INTO missions (company_id, vehicle_id, driver_id, start_date, end_date, start_location, end_location, distance, status, created_at)
                    VALUES (:company_id, :vehicle_id, :driver_id, :start_date, :end_date, :start_location, :end_location, :distance, :status, NOW())";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->bindParam(':vehicle_id', $input['vehicle_id']);
            $stmt->bindParam(':driver_id', $input['driver_id']);
            $stmt->bindParam(':start_date', $input['start_date']);
            $endDate = $input['end_date'] ?? null;
            $stmt->bindParam(':end_date', $endDate);
            $startLocation = $input['start_location'] ?? null;
            $stmt->bindParam(':start_location', $startLocation);
            $endLocation = $input['end_location'] ?? null;
            $stmt->bindParam(':end_location', $endLocation);
            $distance = $input['distance'] ?? null;
            $stmt->bindParam(':distance', $distance);
            $status = $input['status'] ?? 'pending';
            $stmt->bindParam(':status', $status);

            $stmt->execute();
            $missionId = $db->lastInsertId();

            http_response_code(201);
            echo json_encode(['error' => false, 'message' => 'Mission created successfully', 'data' => ['id' => $missionId]]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function update($params)
    {
        $id = $params['id'];
        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $checkSql = "SELECT id FROM missions WHERE id = :id AND company_id = :company_id LIMIT 1";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->bindParam(':company_id', $companyId);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => true, 'message' => 'Mission not found']);
                return;
            }

            $fields = [];
            $values = [':id' => $id, ':company_id' => $companyId];
            $allowedFields = ['vehicle_id', 'driver_id', 'start_date', 'end_date', 'start_location', 'end_location', 'distance', 'status'];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $fields[] = "$field = :$field";
                    $values[":$field"] = $input[$field];
                }
            }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => 'No fields to update']);
                return;
            }

            $sql = "UPDATE missions SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id AND company_id = :company_id";
            $stmt = $db->prepare($sql);
            $stmt->execute($values);

            http_response_code(200);
            echo json_encode(['error' => false, 'message' => 'Mission updated successfully']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function updateStatus($params)
    {
        $id = $params['id'];
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['status'])) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Status is required']);
            return;
        }

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $sql = "UPDATE missions SET status = :status, updated_at = NOW() WHERE id = :id AND company_id = :company_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':status', $input['status']);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => true, 'message' => 'Mission not found']);
                return;
            }

            http_response_code(200);
            echo json_encode(['error' => false, 'message' => 'Mission status updated successfully']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }
}
