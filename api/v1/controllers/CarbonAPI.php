<?php

/**
 * Carbon Tracking API Controller
 * Handles carbon footprint operations via API
 *
 * @author DigiParc Team
 * @version 1.0
 */

require_once __DIR__ . '/../../models/CarbonTracking.php';

class CarbonAPI
{
    private $carbonModel;

    public function __construct()
    {
        $this->carbonModel = new CarbonTracking();
    }

    public function footprint($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $period = $_GET['period'] ?? 'month';

            $footprint = $this->carbonModel->getCompanyFootprint($companyId, $period);

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $footprint]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function recommendations($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];

            $recommendations = $this->carbonModel->getRecommendations($companyId);

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $recommendations]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }
}
