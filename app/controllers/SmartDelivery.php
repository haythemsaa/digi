<?php
/**
 * Smart Delivery Controller
 * AI-powered intelligent delivery system
 */

class SmartDelivery extends Controller {
    private $deliveryModel;
    private $vehicleModel;
    private $clientModel;

    public function __construct() {
        $this->deliveryModel = $this->model('SmartDelivery');
        $this->vehicleModel = $this->model('Vehicle');
        $this->clientModel = $this->model('Client');
    }

    /**
     * Dashboard - Main overview
     */
    public function index() {
        $stats = $this->deliveryModel->getDeliveryStats();
        $recentRoutes = $this->deliveryModel->getAllRoutes(['limit' => 10]);

        $data = [
            'page_title' => 'Smart Delivery - AI Optimization',
            'stats' => $stats,
            'recent_routes' => $recentRoutes
        ];

        $this->view('smart_delivery/index', $data);
    }

    // ==================== PACKAGES ====================

    /**
     * List all packages
     */
    public function packages() {
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $packages = $this->deliveryModel->getAllPackages($filters);

        $data = [
            'page_title' => 'Package Management',
            'packages' => $packages
        ];

        $this->view('smart_delivery/packages', $data);
    }

    /**
     * Add new package
     */
    public function addPackage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $packageId = $this->deliveryModel->createPackage($_POST);

            if ($packageId) {
                $_SESSION['success'] = 'Package created successfully';
                $this->redirect('smart_delivery/packages');
            } else {
                $_SESSION['error'] = 'Failed to create package';
            }
        }

        $data = [
            'page_title' => 'New Package',
            'clients' => $this->clientModel->getAllClients()
        ];

        $this->view('smart_delivery/add_package', $data);
    }

    /**
     * View package details
     */
    public function viewPackage($id) {
        $package = $this->deliveryModel->getPackageById($id);

        if (!$package) {
            $_SESSION['error'] = 'Package not found';
            $this->redirect('smart_delivery/packages');
        }

        $data = [
            'page_title' => 'Package Details',
            'package' => $package
        ];

        $this->view('smart_delivery/view_package', $data);
    }

    // ==================== ROUTES ====================

    /**
     * List all routes
     */
    public function routes() {
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['date'])) {
            $filters['date'] = $_GET['date'];
        }

        $routes = $this->deliveryModel->getAllRoutes($filters);

        $data = [
            'page_title' => 'Delivery Routes',
            'routes' => $routes
        ];

        $this->view('smart_delivery/routes', $data);
    }

    /**
     * Create new route
     */
    public function createRoute() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $routeId = $this->deliveryModel->createRoute($_POST);

            if ($routeId) {
                $_SESSION['success'] = 'Route created successfully';
                $this->redirect('smart_delivery/editRoute/' . $routeId);
            } else {
                $_SESSION['error'] = 'Failed to create route';
            }
        }

        $data = [
            'page_title' => 'Create New Route',
            'vehicles' => $this->vehicleModel->getAllVehicles(['status' => 'available']),
            'drivers' => $this->getDrivers()
        ];

        $this->view('smart_delivery/create_route', $data);
    }

    /**
     * Edit route - Add packages
     */
    public function editRoute($id) {
        $route = $this->deliveryModel->getRouteById($id);

        if (!$route) {
            $_SESSION['error'] = 'Route not found';
            $this->redirect('smart_delivery/routes');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_package'])) {
            $packageId = $_POST['package_id'];
            $sequence = count($route['stops']) + 1;

            if ($this->deliveryModel->addPackageToRoute($id, $packageId, $sequence)) {
                $_SESSION['success'] = 'Package added to route';
                $this->redirect('smart_delivery/editRoute/' . $id);
            } else {
                $_SESSION['error'] = 'Failed to add package';
            }
        }

        $pendingPackages = $this->deliveryModel->getPendingPackages();

        $data = [
            'page_title' => 'Edit Route - ' . $route['route_number'],
            'route' => $route,
            'pending_packages' => $pendingPackages
        ];

        $this->view('smart_delivery/edit_route', $data);
    }

    /**
     * View route details
     */
    public function viewRoute($id) {
        $route = $this->deliveryModel->getRouteById($id);

        if (!$route) {
            $_SESSION['error'] = 'Route not found';
            $this->redirect('smart_delivery/routes');
        }

        $data = [
            'page_title' => 'Route Details - ' . $route['route_number'],
            'route' => $route
        ];

        $this->view('smart_delivery/view_route', $data);
    }

    // ==================== AI OPTIMIZATION ====================

    /**
     * Optimize route using AI
     */
    public function optimizeRoute($id) {
        $result = $this->deliveryModel->optimizeRoute($id);

        if ($result['success']) {
            $_SESSION['success'] = sprintf(
                'Route optimized! Distance saved: %.2f km (%.1f%% improvement)',
                $result['distance_saved'],
                $result['improvement_percentage']
            );
        } else {
            $_SESSION['error'] = 'Optimization failed: ' . ($result['error'] ?? 'Unknown error');
        }

        $this->redirect('smart_delivery/viewRoute/' . $id);
    }

    /**
     * Optimize loading using 3D bin packing
     */
    public function optimizeLoading($id) {
        $result = $this->deliveryModel->optimizeLoading($id);

        if ($result['success']) {
            $_SESSION['success'] = sprintf(
                'Loading optimized! Space utilization: %.1f%% - %d/%d packages fitted',
                $result['space_utilization'],
                $result['fitted_count'],
                $result['total_count']
            );
        } else {
            $_SESSION['error'] = 'Loading optimization failed: ' . ($result['error'] ?? 'Unknown error');
        }

        $this->redirect('smart_delivery/loadingPlan/' . $id);
    }

    /**
     * Complete optimization - Route + Loading
     */
    public function completeOptimization($id) {
        // First optimize route
        $routeResult = $this->deliveryModel->optimizeRoute($id);

        // Then optimize loading
        $loadingResult = $this->deliveryModel->optimizeLoading($id);

        if ($routeResult['success'] && $loadingResult['success']) {
            $_SESSION['success'] = sprintf(
                'Complete optimization done!<br>Route: %.1f%% better<br>Loading: %.1f%% space used',
                $routeResult['improvement_percentage'],
                $loadingResult['space_utilization']
            );
        } else {
            $_SESSION['error'] = 'Optimization failed';
        }

        $this->redirect('smart_delivery/viewRoute/' . $id);
    }

    // ==================== LOADING PLAN ====================

    /**
     * View loading plan for warehouse staff
     */
    public function loadingPlan($id) {
        $route = $this->deliveryModel->getRouteById($id);

        if (!$route) {
            $_SESSION['error'] = 'Route not found';
            $this->redirect('smart_delivery/routes');
        }

        $loadingPlan = $this->deliveryModel->getLoadingPlan($id);

        $data = [
            'page_title' => 'Loading Plan - ' . $route['route_number'],
            'route' => $route,
            'loading_plan' => $loadingPlan
        ];

        $this->view('smart_delivery/loading_plan', $data);
    }

    /**
     * Warehouse interface - Step-by-step loading
     */
    public function warehouseInterface($id) {
        $route = $this->deliveryModel->getRouteById($id);

        if (!$route) {
            $_SESSION['error'] = 'Route not found';
            $this->redirect('smart_delivery/routes');
        }

        $loadingPlan = $this->deliveryModel->getLoadingPlan($id);

        if (!$loadingPlan) {
            $_SESSION['error'] = 'No loading plan found. Please optimize loading first.';
            $this->redirect('smart_delivery/viewRoute/' . $id);
        }

        $data = [
            'page_title' => 'Warehouse Loading Interface',
            'route' => $route,
            'loading_plan' => $loadingPlan
        ];

        $this->view('smart_delivery/warehouse_interface', $data);
    }

    /**
     * Mark instruction as completed (AJAX)
     */
    public function completeInstruction() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            exit;
        }

        $instructionId = $_POST['instruction_id'] ?? null;

        if (!$instructionId) {
            echo json_encode(['success' => false, 'error' => 'Missing instruction ID']);
            exit;
        }

        // Update instruction
        $this->deliveryModel->db->query("UPDATE loading_instructions
                                         SET is_completed = 1,
                                             completed_at = NOW(),
                                             completed_by = ?
                                         WHERE id = ?");
        $this->deliveryModel->db->bind(1, $_SESSION['user_id'] ?? null);
        $this->deliveryModel->db->bind(2, $instructionId);

        if ($this->deliveryModel->db->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit;
    }

    // ==================== DRIVER APP API ====================

    /**
     * Get route for driver (API endpoint)
     */
    public function driverRoute($id) {
        header('Content-Type: application/json');

        $route = $this->deliveryModel->getRouteById($id);

        if (!$route) {
            echo json_encode(['success' => false, 'error' => 'Route not found']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'route' => $route
        ]);
        exit;
    }

    /**
     * Update stop status (API endpoint)
     */
    public function updateStopStatus() {
        header('Content-Type: application/json');

        $stopId = $_POST['stop_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $proof = $_POST['proof'] ?? null;
        $signature = $_POST['signature'] ?? null;

        if (!$stopId || !$status) {
            echo json_encode(['success' => false, 'error' => 'Missing parameters']);
            exit;
        }

        $this->deliveryModel->db->query("UPDATE delivery_stops
                                         SET status = ?,
                                             actual_arrival = NOW(),
                                             delivery_proof = ?,
                                             signature = ?
                                         WHERE id = ?");

        $this->deliveryModel->db->bind(1, $status);
        $this->deliveryModel->db->bind(2, $proof);
        $this->deliveryModel->db->bind(3, $signature);
        $this->deliveryModel->db->bind(4, $stopId);

        if ($this->deliveryModel->db->execute()) {
            // Update package status
            if ($status == 'delivered') {
                $this->deliveryModel->db->query("UPDATE packages p
                                                 INNER JOIN delivery_stops ds ON p.id = ds.package_id
                                                 SET p.status = 'delivered'
                                                 WHERE ds.id = ?");
                $this->deliveryModel->db->bind(1, $stopId);
                $this->deliveryModel->db->execute();
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit;
    }

    // ==================== ANALYTICS ====================

    /**
     * Performance analytics
     */
    public function analytics() {
        // Get performance data
        $this->deliveryModel->db->query("SELECT * FROM delivery_performance
                                         ORDER BY date DESC LIMIT 30");
        $performance = $this->deliveryModel->db->resultSet();

        // Calculate KPIs
        $kpis = $this->calculateKPIs($performance);

        $data = [
            'page_title' => 'Delivery Analytics & Performance',
            'performance' => $performance,
            'kpis' => $kpis
        ];

        $this->view('smart_delivery/analytics', $data);
    }

    /**
     * Calculate KPIs
     */
    private function calculateKPIs($performance) {
        if (empty($performance)) {
            return [
                'avg_success_rate' => 0,
                'avg_efficiency' => 0,
                'total_distance_saved' => 0,
                'total_fuel_saved' => 0
            ];
        }

        $totalSuccessRate = 0;
        $totalDistancePlanned = 0;
        $totalDistanceActual = 0;
        $totalFuelCost = 0;
        $count = count($performance);

        foreach ($performance as $perf) {
            $totalSuccessRate += floatval($perf['delivery_success_rate']);
            $totalDistancePlanned += floatval($perf['planned_distance']);
            $totalDistanceActual += floatval($perf['actual_distance']);
            $totalFuelCost += floatval($perf['fuel_cost']);
        }

        $distanceSaved = $totalDistancePlanned - $totalDistanceActual;

        return [
            'avg_success_rate' => round($totalSuccessRate / $count, 2),
            'avg_efficiency' => round(($distanceSaved / $totalDistancePlanned) * 100, 2),
            'total_distance_saved' => round($distanceSaved, 2),
            'total_fuel_saved' => round($totalFuelCost * 0.25, 2) // Estimate 25% fuel saving
        ];
    }

    /**
     * Get drivers list
     */
    private function getDrivers() {
        $userModel = $this->model('User');
        $allUsers = $userModel->getAllUsers();

        // Filter for drivers
        return array_filter($allUsers, function($user) {
            return $user['role'] === 'driver';
        });
    }

    /**
     * Export route to CSV
     */
    public function exportRoute($id) {
        $route = $this->deliveryModel->getRouteById($id);

        if (!$route) {
            $_SESSION['error'] = 'Route not found';
            $this->redirect('smart_delivery/routes');
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="route_' . $route['route_number'] . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, [
            'Stop #',
            'Package Number',
            'Address',
            'Contact',
            'Phone',
            'Weight (kg)',
            'Time Window',
            'Status'
        ]);

        // Data
        foreach ($route['stops'] as $stop) {
            fputcsv($output, [
                $stop['stop_sequence'],
                $stop['package_number'],
                $stop['address'],
                $stop['delivery_contact'],
                $stop['delivery_phone'],
                $stop['weight'],
                ($stop['time_window_start'] ?? '') . ' - ' . ($stop['time_window_end'] ?? ''),
                $stop['status']
            ]);
        }

        fclose($output);
        exit;
    }
}
