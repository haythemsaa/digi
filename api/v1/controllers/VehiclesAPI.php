<?php

/**
 * Vehicles API Controller
 * Handles vehicle CRUD operations via API
 *
 * @author DigiParc Team
 * @version 1.0
 */

class VehiclesAPI
{
    /**
     * Get all vehicles
     */
    public function index($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = ($page - 1) * $limit;

            $db = Database::getInstance()->getConnection();

            // Count total
            $countSql = "SELECT COUNT(*) as total FROM vehicles WHERE company_id = :company_id";
            $countStmt = $db->prepare($countSql);
            $countStmt->bindParam(':company_id', $companyId);
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get vehicles
            $sql = "SELECT * FROM vehicles WHERE company_id = :company_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'data' => $vehicles,
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
     * Get single vehicle
     */
    public function show($params)
    {
        try {
            $id = $params['id'];
            $companyId = $_SESSION['company_id'];

            $db = Database::getInstance()->getConnection();

            $sql = "SELECT * FROM vehicles WHERE id = :id AND company_id = :company_id LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vehicle) {
                http_response_code(404);
                echo json_encode([
                    'error' => true,
                    'message' => 'Vehicle not found'
                ]);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'data' => $vehicle
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
     * Create vehicle
     */
    public function create($params = [])
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['registration']) || !isset($input['make']) || !isset($input['model'])) {
            http_response_code(400);
            echo json_encode([
                'error' => true,
                'message' => 'Registration, make, and model are required'
            ]);
            return;
        }

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $sql = "INSERT INTO vehicles (company_id, registration, make, model, year, fuel_type, status, mileage, created_at)
                    VALUES (:company_id, :registration, :make, :model, :year, :fuel_type, :status, :mileage, NOW())";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->bindParam(':registration', $input['registration']);
            $stmt->bindParam(':make', $input['make']);
            $stmt->bindParam(':model', $input['model']);
            $year = $input['year'] ?? null;
            $stmt->bindParam(':year', $year);
            $fuelType = $input['fuel_type'] ?? 'diesel';
            $stmt->bindParam(':fuel_type', $fuelType);
            $status = $input['status'] ?? 'active';
            $stmt->bindParam(':status', $status);
            $mileage = $input['mileage'] ?? 0;
            $stmt->bindParam(':mileage', $mileage);

            $stmt->execute();

            $vehicleId = $db->lastInsertId();

            http_response_code(201);
            echo json_encode([
                'error' => false,
                'message' => 'Vehicle created successfully',
                'data' => ['id' => $vehicleId]
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
     * Update vehicle
     */
    public function update($params)
    {
        $id = $params['id'];
        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            // Check if vehicle exists
            $checkSql = "SELECT id FROM vehicles WHERE id = :id AND company_id = :company_id LIMIT 1";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->bindParam(':company_id', $companyId);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode([
                    'error' => true,
                    'message' => 'Vehicle not found'
                ]);
                return;
            }

            // Build update query dynamically
            $fields = [];
            $values = [':id' => $id, ':company_id' => $companyId];

            $allowedFields = ['registration', 'make', 'model', 'year', 'fuel_type', 'status', 'mileage'];

            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $fields[] = "$field = :$field";
                    $values[":$field"] = $input[$field];
                }
            }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode([
                    'error' => true,
                    'message' => 'No fields to update'
                ]);
                return;
            }

            $sql = "UPDATE vehicles SET " . implode(', ', $fields) . ", updated_at = NOW()
                    WHERE id = :id AND company_id = :company_id";

            $stmt = $db->prepare($sql);
            $stmt->execute($values);

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'message' => 'Vehicle updated successfully'
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
     * Delete vehicle
     */
    public function delete($params)
    {
        $id = $params['id'];

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $sql = "DELETE FROM vehicles WHERE id = :id AND company_id = :company_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode([
                    'error' => true,
                    'message' => 'Vehicle not found'
                ]);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'message' => 'Vehicle deleted successfully'
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
}
