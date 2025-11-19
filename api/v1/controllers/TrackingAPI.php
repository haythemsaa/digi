<?php

/**
 * Tracking API Controller
 * Handles GPS tracking operations via API
 *
 * @author Pakiparc Team
 * @version 1.0
 */

class TrackingAPI
{
    public function live($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            // Get last position for each vehicle (within last 30 minutes)
            $sql = "SELECT t.*, v.registration, v.make, v.model
                    FROM (
                        SELECT vehicle_id, MAX(timestamp) as last_timestamp
                        FROM tracking
                        WHERE company_id = :company_id
                        AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                        GROUP BY vehicle_id
                    ) latest
                    INNER JOIN tracking t ON t.vehicle_id = latest.vehicle_id AND t.timestamp = latest.last_timestamp
                    INNER JOIN vehicles v ON v.id = t.vehicle_id
                    WHERE t.company_id = :company_id";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $positions]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function recordPosition($params = [])
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['vehicle_id']) || !isset($input['latitude']) || !isset($input['longitude'])) {
            http_response_code(400);
            echo json_encode(['error' => true, 'message' => 'Vehicle ID, latitude, and longitude are required']);
            return;
        }

        try {
            $companyId = $_SESSION['company_id'];
            $db = Database::getInstance()->getConnection();

            $sql = "INSERT INTO tracking (company_id, vehicle_id, latitude, longitude, speed, heading, timestamp)
                    VALUES (:company_id, :vehicle_id, :latitude, :longitude, :speed, :heading, NOW())";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->bindParam(':vehicle_id', $input['vehicle_id']);
            $stmt->bindParam(':latitude', $input['latitude']);
            $stmt->bindParam(':longitude', $input['longitude']);
            $speed = $input['speed'] ?? null;
            $stmt->bindParam(':speed', $speed);
            $heading = $input['heading'] ?? null;
            $stmt->bindParam(':heading', $heading);

            $stmt->execute();

            http_response_code(201);
            echo json_encode(['error' => false, 'message' => 'Position recorded successfully']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function history($params)
    {
        try {
            $vehicleId = $params['vehicleId'];
            $companyId = $_SESSION['company_id'];

            $startDate = $_GET['start_date'] ?? date('Y-m-d 00:00:00', strtotime('-1 day'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d 23:59:59');

            $db = Database::getInstance()->getConnection();

            $sql = "SELECT * FROM tracking
                    WHERE company_id = :company_id
                    AND vehicle_id = :vehicle_id
                    AND timestamp BETWEEN :start_date AND :end_date
                    ORDER BY timestamp ASC";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->bindParam(':vehicle_id', $vehicleId);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();

            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $history]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }
}
