<?php
/**
 * Dashboard Controller
 */

class Dashboard extends Controller {

    private $userModel;
    private $vehicleModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->vehicleModel = $this->model('Vehicle');
    }

    public function index() {
        // Get dashboard statistics
        $data = [
            'user' => $_SESSION,
            'stats' => $this->getDashboardStats()
        ];

        $this->view('dashboard/index', $data);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats() {
        $stats = [];

        // Total vehicles
        $stats['total_vehicles'] = $this->vehicleModel->countVehicles();
        $stats['active_vehicles'] = $this->vehicleModel->countVehiclesByStatus('active');
        $stats['maintenance_vehicles'] = $this->vehicleModel->countVehiclesByStatus('maintenance');

        // Total users
        $stats['total_users'] = $this->userModel->countUsers();

        // TODO: Add more stats from other modules
        // - Active transport orders
        // - Pending maintenance
        // - Recent alerts
        // - Fuel consumption this month
        // - etc.

        return $stats;
    }
}
