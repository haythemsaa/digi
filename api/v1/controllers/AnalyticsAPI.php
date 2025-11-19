<?php

/**
 * Analytics API Controller
 * Handles analytics and KPIs via API
 *
 * @author Pakiparc Team
 * @version 1.0
 */

require_once __DIR__ . '/../../models/Analytics.php';

class AnalyticsAPI
{
    private $analyticsModel;

    public function __construct()
    {
        $this->analyticsModel = new Analytics();
    }

    public function dashboard($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $period = $_GET['period'] ?? 'month';

            $kpis = $this->analyticsModel->getDashboardKPIs($companyId, $period);

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $kpis]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function kpis($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $period = $_GET['period'] ?? 'month';
            $category = $_GET['category'] ?? null;

            if ($category) {
                switch ($category) {
                    case 'fleet':
                        $data = $this->analyticsModel->getFleetKPIs($companyId, $this->getPeriodFilter($period));
                        break;
                    case 'financial':
                        $data = $this->analyticsModel->getFinancialKPIs($companyId, $this->getPeriodFilter($period));
                        break;
                    case 'operations':
                        $data = $this->analyticsModel->getOperationsKPIs($companyId, $this->getPeriodFilter($period));
                        break;
                    default:
                        $data = [];
                }
            } else {
                $data = $this->analyticsModel->getDashboardKPIs($companyId, $period);
            }

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $data]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    public function trends($params = [])
    {
        try {
            $companyId = $_SESSION['company_id'];
            $metric = $_GET['metric'] ?? 'missions';
            $period = $_GET['period'] ?? 'month';

            $data = $this->analyticsModel->getTrends($companyId, $metric, $period);

            http_response_code(200);
            echo json_encode(['error' => false, 'data' => $data]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Server error', 'details' => DEBUG ? $e->getMessage() : null]);
        }
    }

    private function getPeriodFilter($period)
    {
        switch ($period) {
            case 'today':
                return "DATE(created_at) = CURDATE()";
            case 'week':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            case 'month':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            case 'quarter':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
            case 'year':
                return "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        }
    }
}
