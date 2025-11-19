<?php

/**
 * Analytics Controller
 * Handles advanced analytics and reporting features
 *
 * @author DigiParc Team
 * @version 1.0
 */
class AnalyticsController
{
    private $analyticsModel;
    private $companyId;

    public function __construct()
    {
        // Require authentication
        requireAuth();

        // Require company context
        CompanyMiddleware::requireCompanyContext();

        $this->analyticsModel = new Analytics();
        $this->companyId = getCurrentCompanyId();
    }

    /**
     * Display advanced analytics dashboard
     */
    public function dashboard()
    {
        $period = $_GET['period'] ?? 'month';
        $validPeriods = ['day', 'week', 'month', 'quarter', 'year'];

        if (!in_array($period, $validPeriods)) {
            $period = 'month';
        }

        // Get all KPIs
        $kpis = $this->analyticsModel->getDashboardKPIs($this->companyId, $period);

        // Get trend data for charts
        $trendData = [
            'costs' => $this->analyticsModel->getTrendData($this->companyId, 'costs', $period),
            'missions' => $this->analyticsModel->getTrendData($this->companyId, 'missions', $period),
            'fuel' => $this->analyticsModel->getTrendData($this->companyId, 'fuel', $period),
            'maintenance' => $this->analyticsModel->getTrendData($this->companyId, 'maintenance', $period)
        ];

        // Get top performers
        $topVehicles = $this->analyticsModel->getVehiclePerformance($this->companyId, 5);
        $topDrivers = $this->analyticsModel->getDriverPerformance($this->companyId, 5);

        // Get company info for display
        $company = getCurrentCompany();

        $data = [
            'title' => 'Tableau de Bord Analytique',
            'period' => $period,
            'kpis' => $kpis,
            'trends' => $trendData,
            'top_vehicles' => $topVehicles,
            'top_drivers' => $topDrivers,
            'company' => $company
        ];

        view('analytics/dashboard', $data);
    }

    /**
     * Get KPIs data (AJAX endpoint)
     */
    public function getKPIs()
    {
        header('Content-Type: application/json');

        $period = $_GET['period'] ?? 'month';
        $kpis = $this->analyticsModel->getDashboardKPIs($this->companyId, $period);

        echo json_encode([
            'success' => true,
            'data' => $kpis
        ]);
    }

    /**
     * Get trend data (AJAX endpoint)
     */
    public function getTrend()
    {
        header('Content-Type: application/json');

        $metric = $_GET['metric'] ?? 'costs';
        $period = $_GET['period'] ?? 'month';

        $data = $this->analyticsModel->getTrendData($this->companyId, $metric, $period);

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get vehicle performance (AJAX endpoint)
     */
    public function getVehiclePerformance()
    {
        header('Content-Type: application/json');

        $limit = (int)($_GET['limit'] ?? 10);
        $data = $this->analyticsModel->getVehiclePerformance($this->companyId, $limit);

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get driver performance (AJAX endpoint)
     */
    public function getDriverPerformance()
    {
        header('Content-Type: application/json');

        $limit = (int)($_GET['limit'] ?? 10);
        $data = $this->analyticsModel->getDriverPerformance($this->companyId, $limit);

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Export dashboard as PDF
     */
    public function exportPDF()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $period = $_GET['period'] ?? 'month';
        $kpis = $this->analyticsModel->getDashboardKPIs($this->companyId, $period);
        $company = getCurrentCompany();

        // Generate HTML
        ob_start();
        include __DIR__ . '/../views/analytics/pdf_template.php';
        $html = ob_get_clean();

        // Create PDF
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output PDF
        $filename = 'dashboard_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * Export dashboard as Excel
     */
    public function exportExcel()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $period = $_GET['period'] ?? 'month';
        $kpis = $this->analyticsModel->getDashboardKPIs($this->companyId, $period);
        $company = getCurrentCompany();

        // Create new Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'DigiParc - Tableau de Bord Analytique');
        $sheet->setCellValue('A2', 'Entreprise: ' . $company['name']);
        $sheet->setCellValue('A3', 'Période: ' . ucfirst($period));
        $sheet->setCellValue('A4', 'Date: ' . date('d/m/Y H:i'));

        // Fleet KPIs
        $row = 6;
        $sheet->setCellValue('A' . $row, 'INDICATEURS FLOTTE');
        $row++;
        $sheet->setCellValue('A' . $row, 'Total véhicules');
        $sheet->setCellValue('B' . $row, $kpis['fleet']['total_vehicles']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Véhicules actifs');
        $sheet->setCellValue('B' . $row, $kpis['fleet']['active_vehicles']);
        $row++;
        $sheet->setCellValue('A' . $row, 'En maintenance');
        $sheet->setCellValue('B' . $row, $kpis['fleet']['in_maintenance']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Âge moyen (années)');
        $sheet->setCellValue('B' . $row, $kpis['fleet']['avg_age']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Taux d\'utilisation (%)');
        $sheet->setCellValue('B' . $row, $kpis['fleet']['utilization_rate']);

        // Financial KPIs
        $row += 2;
        $sheet->setCellValue('A' . $row, 'INDICATEURS FINANCIERS');
        $row++;
        $sheet->setCellValue('A' . $row, 'Chiffre d\'affaires');
        $sheet->setCellValue('B' . $row, $kpis['financial']['total_revenue']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Coûts totaux');
        $sheet->setCellValue('B' . $row, $kpis['financial']['total_costs']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Bénéfice');
        $sheet->setCellValue('B' . $row, $kpis['financial']['profit']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Marge (%)');
        $sheet->setCellValue('B' . $row, $kpis['financial']['profit_margin']);

        // Operations KPIs
        $row += 2;
        $sheet->setCellValue('A' . $row, 'INDICATEURS OPÉRATIONNELS');
        $row++;
        $sheet->setCellValue('A' . $row, 'Total missions');
        $sheet->setCellValue('B' . $row, $kpis['operations']['total_missions']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Missions terminées');
        $sheet->setCellValue('B' . $row, $kpis['operations']['completed_missions']);
        $row++;
        $sheet->setCellValue('A' . $row, 'Taux de complétion (%)');
        $sheet->setCellValue('B' . $row, $kpis['operations']['completion_rate']);

        // Style
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A6:A' . $row)->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);

        // Output Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="dashboard_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
