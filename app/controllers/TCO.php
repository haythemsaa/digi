<?php
/**
 * TCO Controller
 * Manages Total Cost of Ownership calculations for vehicles
 */

class TCO extends Controller {
    private $tcoModel;
    private $vehicleModel;

    public function __construct() {
        $this->tcoModel = $this->model('TCO');
        $this->vehicleModel = $this->model('Vehicle');
    }

    /**
     * Index - TCO Dashboard
     */
    public function index() {
        $calculations = $this->tcoModel->getAllCalculations();
        $stats = $this->tcoModel->getTCOStats();
        $fleetReport = $this->tcoModel->getFleetTCOReport();

        $data = [
            'page_title' => 'Total Cost of Ownership (TCO)',
            'calculations' => $calculations,
            'stats' => $stats,
            'fleet_report' => $fleetReport
        ];

        $this->view('tco/index', $data);
    }

    /**
     * TCO Calculator
     */
    public function calculator() {
        $tcoResult = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vehicleId = $_POST['vehicle_id'] ?? null;
            $configurationId = $_POST['configuration_id'] ?? null;

            // Custom parameters
            $customParams = [];
            if (isset($_POST['use_custom']) && $_POST['use_custom'] == '1') {
                $customParams = [
                    'depreciation_rate' => $_POST['depreciation_rate'] ?? null,
                    'fuel_cost_per_km' => $_POST['fuel_cost_per_km'] ?? null,
                    'maintenance_cost_per_km' => $_POST['maintenance_cost_per_km'] ?? null,
                    'insurance_annual' => $_POST['insurance_annual'] ?? null,
                    'tax_annual' => $_POST['tax_annual'] ?? null,
                    'parking_annual' => $_POST['parking_annual'] ?? null,
                    'driver_salary_annual' => $_POST['driver_salary_annual'] ?? null,
                    'admin_cost_annual' => $_POST['admin_cost_annual'] ?? null,
                    'average_annual_km' => $_POST['average_annual_km'] ?? null,
                    'expected_lifetime_years' => $_POST['expected_lifetime_years'] ?? null,
                    'residual_value_percentage' => $_POST['residual_value_percentage'] ?? null
                ];
                // Remove null values
                $customParams = array_filter($customParams, function($value) {
                    return $value !== null && $value !== '';
                });
            }

            $tcoResult = $this->tcoModel->calculateTCO($vehicleId, $configurationId, $customParams);

            // Save calculation if requested
            if (isset($_POST['save_calculation']) && $_POST['save_calculation'] == '1' && $tcoResult && !isset($tcoResult['error'])) {
                $calculationId = $this->tcoModel->saveCalculation($tcoResult);
                if ($calculationId) {
                    $_SESSION['success'] = 'TCO calculation saved successfully';
                    $this->redirect('tco/view/' . $calculationId);
                }
            }
        }

        $data = [
            'page_title' => 'TCO Calculator',
            'vehicles' => $this->vehicleModel->getAllVehicles(),
            'configurations' => $this->tcoModel->getAllConfigurations(),
            'tco_result' => $tcoResult
        ];

        $this->view('tco/calculator', $data);
    }

    /**
     * View TCO calculation
     */
    public function view($id) {
        $calculation = $this->tcoModel->getCalculationById($id);

        if (!$calculation) {
            $_SESSION['error'] = 'TCO calculation not found';
            $this->redirect('tco');
        }

        $data = [
            'page_title' => 'TCO Calculation Details',
            'calculation' => $calculation
        ];

        $this->view('tco/view', $data);
    }

    /**
     * Compare multiple vehicles
     */
    public function compare() {
        $comparisons = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vehicle_ids'])) {
            $vehicleIds = $_POST['vehicle_ids'];
            if (is_array($vehicleIds) && count($vehicleIds) > 1) {
                $comparisons = $this->tcoModel->compareTCO($vehicleIds);
            } else {
                $_SESSION['error'] = 'Please select at least 2 vehicles to compare';
            }
        }

        $data = [
            'page_title' => 'Compare TCO',
            'vehicles' => $this->vehicleModel->getAllVehicles(),
            'comparisons' => $comparisons
        ];

        $this->view('tco/compare', $data);
    }

    /**
     * Fleet TCO Report
     */
    public function fleetReport() {
        $fleetReport = $this->tcoModel->getFleetTCOReport();
        $stats = $this->tcoModel->getTCOStats();

        $data = [
            'page_title' => 'Fleet TCO Report',
            'fleet_report' => $fleetReport,
            'stats' => $stats
        ];

        $this->view('tco/fleet_report', $data);
    }

    // ==================== CONFIGURATIONS ====================

    /**
     * TCO configurations list
     */
    public function configurations() {
        $configurations = $this->tcoModel->getAllConfigurations();

        $data = [
            'page_title' => 'TCO Configurations',
            'configurations' => $configurations
        ];

        $this->view('tco/configurations', $data);
    }

    /**
     * Add TCO configuration
     */
    public function addConfiguration() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->tcoModel->createConfiguration($_POST)) {
                $_SESSION['success'] = 'TCO configuration created successfully';
                $this->redirect('tco/configurations');
            } else {
                $_SESSION['error'] = 'Failed to create TCO configuration';
            }
        }

        $data = [
            'page_title' => 'New TCO Configuration',
            'vehicle_types' => $this->getVehicleTypes()
        ];

        $this->view('tco/add_configuration', $data);
    }

    /**
     * Edit TCO configuration
     */
    public function editConfiguration($id) {
        $configuration = $this->tcoModel->getConfigurationById($id);

        if (!$configuration) {
            $_SESSION['error' ] = 'TCO configuration not found';
            $this->redirect('tco/configurations');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->tcoModel->updateConfiguration($id, $_POST)) {
                $_SESSION['success'] = 'TCO configuration updated successfully';
                $this->redirect('tco/configurations');
            } else {
                $_SESSION['error'] = 'Failed to update TCO configuration';
            }
        }

        $data = [
            'page_title' => 'Edit TCO Configuration',
            'configuration' => $configuration,
            'vehicle_types' => $this->getVehicleTypes()
        ];

        $this->view('tco/edit_configuration', $data);
    }

    /**
     * Delete TCO configuration
     */
    public function deleteConfiguration($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->tcoModel->deleteConfiguration($id)) {
                $_SESSION['success'] = 'TCO configuration deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete TCO configuration';
            }
        }

        $this->redirect('tco/configurations');
    }

    /**
     * Get configuration by vehicle (AJAX)
     */
    public function getConfigurationByVehicle() {
        header('Content-Type: application/json');

        $vehicleId = $_GET['vehicle_id'] ?? null;

        if ($vehicleId) {
            // Get vehicle details
            $vehicle = $this->vehicleModel->getVehicleById($vehicleId);

            if ($vehicle && isset($vehicle['vehicle_type_id'])) {
                $configuration = $this->tcoModel->getConfigurationByVehicleType($vehicle['vehicle_type_id']);

                if ($configuration) {
                    echo json_encode(['success' => true, 'configuration' => $configuration]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'No configuration found for this vehicle type']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Vehicle not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing vehicle ID']);
        }
        exit;
    }

    /**
     * Calculate TCO for vehicle (AJAX)
     */
    public function calculateForVehicle() {
        header('Content-Type: application/json');

        $vehicleId = $_POST['vehicle_id'] ?? null;
        $configurationId = $_POST['configuration_id'] ?? null;
        $customParams = $_POST['custom_params'] ?? [];

        if ($vehicleId) {
            $tcoResult = $this->tcoModel->calculateTCO($vehicleId, $configurationId, $customParams);

            if ($tcoResult && !isset($tcoResult['error'])) {
                echo json_encode(['success' => true, 'tco' => $tcoResult]);
            } else {
                echo json_encode(['success' => false, 'error' => $tcoResult['error'] ?? 'Calculation failed']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing vehicle ID']);
        }
        exit;
    }

    /**
     * Export TCO report (CSV)
     */
    public function exportReport() {
        $fleetReport = $this->tcoModel->getFleetTCOReport();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tco_fleet_report_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, [
            'Vehicle',
            'Registration',
            'Make',
            'Model',
            'Cost per KM',
            'Monthly Cost',
            'Annual Cost',
            'Fuel Cost',
            'Maintenance Cost'
        ]);

        // Data
        foreach ($fleetReport as $row) {
            fputcsv($output, [
                $row['registration_number'],
                $row['registration_number'],
                $row['make'],
                $row['model'],
                number_format($row['cost_per_km'], 3),
                number_format($row['monthly_cost'], 2),
                number_format($row['total_annual_cost'], 2),
                number_format($row['actual_fuel_cost'], 2),
                number_format($row['actual_maintenance_cost'], 2)
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Helper to get vehicle types
     */
    private function getVehicleTypes() {
        // Simple vehicle types - could be from a database table
        return [
            ['id' => 1, 'name' => 'Car'],
            ['id' => 2, 'name' => 'Van'],
            ['id' => 3, 'name' => 'Truck'],
            ['id' => 4, 'name' => 'Bus'],
            ['id' => 5, 'name' => 'Motorcycle']
        ];
    }
}
