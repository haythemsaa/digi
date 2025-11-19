<?php

/**
 * Drivers API Controller
 * Handles driver CRUD operations via API
 *
 * @author DigiParc Team
 * @version 1.0
 */

class DriversAPI
{
    /**
     * Get all drivers
     */
    public function index($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;

            $db = Database::getInstance()->getConnection();

            $countSql = "SELECT COUNT(*) as total FROM drivers WHERE company_id = :company_id";
            $countStmt = $db->prepare($countSql);
            $countStmt->bindParam(':company_id', $companyId);
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            $sql = "SELECT * FROM drivers WHERE company_id = :company_id ORDER BY last_name, first_name LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'data' => $drivers,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Server error',
                'details' => DEBUG ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Get single driver
     */
    public function show($params)
    {
        try {
            $id = $params['id'];
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT * FROM drivers WHERE id = :id AND company_id = :company_id LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            $driver = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$driver) {
                http_response_code(404);
                echo json_encode(['error' => true, 'message' => 'Driver not found']);
                return;
            }

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $driver]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    /**
     * Create driver
     */
    public function create($params = [])
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['first_name']) || !isset($input['last_name']) || !isset($input['license_number'])) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'First name, last name, and license number are required']);
            return;
        }

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $sql = "INSERT INTO drivers (company_id, first_name, last_name, email, phone, license_number, license_expiry, status, created_at)
                    VALUES (:company_id, :first_name, :last_name, :email, :phone, :license_number, :license_expiry, :status, NOW())";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->bindParam(':first_name', $input['first_name']);
            $stmt->bindParam(':last_name', $input['last_name']);
            $email = $input['email'] ?? null;
            $stmt->bindParam(':email', $email);
            $phone = $input['phone'] ?? null;
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':license_number', $input['license_number']);
            $licenseExpiry = $input['license_expiry'] ?? null;
            $stmt->bindParam(':license_expiry', $licenseExpiry);
            $status = $input['status'] ?? 'active';
            $stmt->bindParam(':status', $status);

            $stmt->execute();
            $driverId = $db->lastInsertId();

            http_response_code(201);
            echo json_encode(['error' => false, 'message' => 'Driver created successfully', 'data' => ['id' => $driverId]]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    /**
     * Update driver
     */
    public function update($params)
    {
        $id = $params['id'];
        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $checkSql = "SELECT id FROM drivers WHERE id = :id AND company_id = :company_id LIMIT 1";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->bindParam(':company_id', $companyId);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => true, 'message' => 'Driver not found']);
                return;
            }

            $fields = [];
            $values = [':id' => $id, ':company_id' => $companyId];
            $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'license_number', 'license_expiry', 'status'];

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

            $sql = "UPDATE drivers SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id AND company_id = :company_id";
            $stmt = $db->prepare($sql);
            $stmt->execute($values);

            http_response_code(200);
            echo json_encode(['error' => false, 'message' => 'Driver updated successfully']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    /**
     * Delete driver
     */
    public function delete($params)
    {
        $id = $params['id'];

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $sql = "DELETE FROM drivers WHERE id = :id AND company_id = :company_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => true, 'message' => 'Driver not found']);
                return;
            }

            http_response_code(200);
            echo json_encode(['error' => false, 'message' => 'Driver deleted successfully']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }
}
