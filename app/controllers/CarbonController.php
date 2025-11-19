<?php

/**
 * Carbon Controller
 * Handles carbon tracking and CSR reporting
 *
 * @author DigiParc Team
 * @version 1.0
 */
class CarbonController
{
    private $carbonModel;
    private $companyId;

    public function __construct()
    {
        requireAuth();
        CompanyMiddleware::requireCompanyContext();

        $this->carbonModel = new CarbonTracking();
        $this->companyId = getCurrentCompanyId();
    }

    /**
     * Carbon tracking dashboard
     */
    public function dashboard()
    {
        $period = $_GET['period'] ?? 'month';

        // Get carbon footprint
        $footprint = $this->carbonModel->getCompanyFootprint($this->companyId, $period);

        // Get trend data
        $trend = $this->carbonModel->getCarbonTrend($this->companyId, 12);

        // Get top performers
        $vehiclePerformance = $this->carbonModel->getVehiclePerformance($this->companyId, 10);
        $driverPerformance = $this->carbonModel->getDriverPerformance($this->companyId, 10);

        // Get recommendations
        $recommendations = $this->carbonModel->getEcoRecommendations($this->companyId);

        $company = getCurrentCompany();

        $data = [
            'title' => 'Suivi Carbone & RSE',
            'period' => $period,
            'footprint' => $footprint,
            'trend' => $trend,
            'vehicle_performance' => $vehiclePerformance,
            'driver_performance' => $driverPerformance,
            'recommendations' => $recommendations,
            'company' => $company
        ];

        view('carbon/dashboard', $data);
    }

    /**
     * Generate CSR report
     */
    public function csrReport()
    {
        $year = $_GET['year'] ?? date('Y');

        $report = $this->carbonModel->generateCSRReport($this->companyId, $year);
        $company = getCurrentCompany();

        $data = [
            'title' => 'Rapport RSE ' . $year,
            'year' => $year,
            'report' => $report,
            'company' => $company
        ];

        view('carbon/csr_report', $data);
    }

    /**
     * Export CSR report as PDF
     */
    public function exportCSRReport()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $year = $_GET['year'] ?? date('Y');
        $report = $this->carbonModel->generateCSRReport($this->companyId, $year);
        $company = getCurrentCompany();

        // Generate HTML
        ob_start();
        include __DIR__ . '/../views/carbon/csr_report_pdf.php';
        $html = ob_get_clean();

        // Create PDF
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output
        $filename = 'rapport_rse_' . $year . '_' . $company['name'] . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * API: Get carbon footprint (AJAX)
     */
    public function getFootprint()
    {
        header('Content-Type: application/json');

        $period = $_GET['period'] ?? 'month';
        $footprint = $this->carbonModel->getCompanyFootprint($this->companyId, $period);

        echo json_encode([
            'success' => true,
            'data' => $footprint
        ]);
    }

    /**
     * API: Get carbon trend (AJAX)
     */
    public function getTrend()
    {
        header('Content-Type: application/json');

        $months = (int)($_GET['months'] ?? 12);
        $trend = $this->carbonModel->getCarbonTrend($this->companyId, $months);

        echo json_encode([
            'success' => true,
            'data' => $trend
        ]);
    }
}
