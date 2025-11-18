<?php
/**
 * Reports Controller
 */

class Reports extends Controller {

    private $vehicleModel;
    private $trackingModel;
    private $transportModel;
    private $maintenanceModel;
    private $financialModel;

    public function __construct() {
        $this->vehicleModel = $this->model('Vehicle');
        $this->trackingModel = $this->model('Tracking');
        $this->transportModel = $this->model('Transport');
        $this->maintenanceModel = $this->model('Maintenance');
        $this->financialModel = $this->model('Financial');
    }

    public function index() {
        $data = [
            'active_menu' => 'reports',
            'page_title' => 'Reports & Analytics'
        ];

        $this->view('reports/index', $data);
    }

    /**
     * Fleet Report
     */
    public function fleet() {
        $vehicles = $this->vehicleModel->getAllVehicles();

        // Vehicle stats by type
        $byType = [];
        $byStatus = [];
        $byFuelType = [];

        foreach ($vehicles as $vehicle) {
            // By type
            if (!isset($byType[$vehicle['type']])) {
                $byType[$vehicle['type']] = 0;
            }
            $byType[$vehicle['type']]++;

            // By status
            if (!isset($byStatus[$vehicle['status']])) {
                $byStatus[$vehicle['status']] = 0;
            }
            $byStatus[$vehicle['status']]++;

            // By fuel type
            if (!isset($byFuelType[$vehicle['fuel_type']])) {
                $byFuelType[$vehicle['fuel_type']] = 0;
            }
            $byFuelType[$vehicle['fuel_type']]++;
        }

        $data = [
            'vehicles' => $vehicles,
            'by_type' => $byType,
            'by_status' => $byStatus,
            'by_fuel_type' => $byFuelType,
            'active_menu' => 'reports',
            'page_title' => 'Fleet Report'
        ];

        $this->view('reports/fleet', $data);
    }

    /**
     * GPS Activity Report
     */
    public function gpsActivity() {
        $activeTrips = $this->trackingModel->getActiveTrips();
        $speedAlerts = $this->trackingModel->getSpeedAlerts(100);

        $data = [
            'active_trips' => $activeTrips,
            'speed_alerts' => $speedAlerts,
            'active_menu' => 'reports',
            'page_title' => 'GPS Activity Report'
        ];

        $this->view('reports/gps_activity', $data);
    }

    /**
     * Transport Report
     */
    public function transport() {
        $orders = $this->transportModel->getAllOrders();
        $quotes = $this->transportModel->getAllQuotes();
        $invoices = $this->transportModel->getAllInvoices();

        // Calculate totals
        $totalRevenue = 0;
        $totalInvoiced = 0;
        $totalPaid = 0;

        foreach ($invoices as $invoice) {
            $totalInvoiced += $invoice['total_amount'];
            $totalPaid += $invoice['paid_amount'];
        }

        foreach ($orders as $order) {
            if ($order['status'] === 'delivered') {
                $totalRevenue += $order['total_amount'];
            }
        }

        $data = [
            'orders' => $orders,
            'quotes' => $quotes,
            'invoices' => $invoices,
            'total_revenue' => $totalRevenue,
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'active_menu' => 'reports',
            'page_title' => 'Transport Report'
        ];

        $this->view('reports/transport', $data);
    }

    /**
     * Maintenance Report
     */
    public function maintenance() {
        $workOrders = $this->maintenanceModel->getAllWorkOrders();
        $dueMaintenances = $this->maintenanceModel->getDueMaintenances();

        // Calculate costs
        $totalCost = 0;
        $laborCost = 0;
        $partsCost = 0;

        foreach ($workOrders as $wo) {
            $totalCost += $wo['total_cost'];
            $laborCost += $wo['labor_cost'];
            $partsCost += $wo['parts_cost'];
        }

        $data = [
            'work_orders' => $workOrders,
            'due_maintenances' => $dueMaintenances,
            'total_cost' => $totalCost,
            'labor_cost' => $laborCost,
            'parts_cost' => $partsCost,
            'active_menu' => 'reports',
            'page_title' => 'Maintenance Report'
        ];

        $this->view('reports/maintenance', $data);
    }

    /**
     * Financial Report
     */
    public function financial() {
        $transactions = $this->financialModel->getAllTransactions(1000);
        $accounts = $this->financialModel->getAllAccounts();
        $summary = $this->financialModel->getFinancialSummary();

        // Monthly breakdown
        $monthlyData = [];
        foreach ($transactions as $transaction) {
            $month = date('Y-m', strtotime($transaction['transaction_date']));
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = ['income' => 0, 'expense' => 0];
            }

            if ($transaction['type'] === 'income') {
                $monthlyData[$month]['income'] += $transaction['amount'];
            } else {
                $monthlyData[$month]['expense'] += $transaction['amount'];
            }
        }

        $data = [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'summary' => $summary,
            'monthly_data' => $monthlyData,
            'active_menu' => 'reports',
            'page_title' => 'Financial Report'
        ];

        $this->view('reports/financial', $data);
    }

    /**
     * Export to CSV
     */
    public function export($type = 'fleet') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        switch ($type) {
            case 'fleet':
                $vehicles = $this->vehicleModel->getAllVehicles();
                fputcsv($output, ['Registration', 'Brand', 'Model', 'Type', 'Year', 'Fuel Type', 'Odometer', 'Status']);
                foreach ($vehicles as $v) {
                    fputcsv($output, [$v['registration_number'], $v['brand'], $v['model'], $v['type'], $v['year'], $v['fuel_type'], $v['odometer'], $v['status']]);
                }
                break;

            case 'transport':
                $orders = $this->transportModel->getAllOrders();
                fputcsv($output, ['Order Number', 'Client', 'Pickup City', 'Delivery City', 'Status', 'Total Amount']);
                foreach ($orders as $o) {
                    fputcsv($output, [$o['order_number'], $o['company_name'], $o['pickup_city'], $o['delivery_city'], $o['status'], $o['total_amount']]);
                }
                break;

            case 'maintenance':
                $workOrders = $this->maintenanceModel->getAllWorkOrders();
                fputcsv($output, ['Reference', 'Vehicle', 'Type', 'Status', 'Priority', 'Total Cost']);
                foreach ($workOrders as $wo) {
                    fputcsv($output, [$wo['reference'], $wo['registration_number'], $wo['type'], $wo['status'], $wo['priority'], $wo['total_cost']]);
                }
                break;
        }

        fclose($output);
        exit();
    }
}
