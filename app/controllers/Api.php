<?php
/**
 * REST API Controller
 * For mobile apps and third-party integrations
 */

class Api extends Controller {

    private $vehicleModel;
    private $trackingModel;
    private $userModel;
    private $transportModel;
    private $maintenanceModel;

    public function __construct() {
        // Allow CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        $this->vehicleModel = $this->model('Vehicle');
        $this->trackingModel = $this->model('Tracking');
        $this->userModel = $this->model('User');
        $this->transportModel = $this->model('Transport');
        $this->maintenanceModel = $this->model('Maintenance');
    }

    /**
     * API Login - Returns token
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            $this->jsonResponse(['error' => 'Email and password required'], 400);
        }

        $user = $this->userModel->login($data['email'], $data['password']);

        if ($user) {
            // Generate simple token (in production, use JWT)
            $token = bin2hex(random_bytes(32));

            // Store token in session or database (simplified for demo)
            $_SESSION['api_token'] = $token;
            $_SESSION['api_user_id'] = $user['id'];

            $this->jsonResponse([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            $this->jsonResponse(['error' => 'Invalid credentials'], 401);
        }
    }

    /**
     * Verify API token
     */
    private function verifyToken() {
        $headers = getallheaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
        }

        if (!$token || !isset($_SESSION['api_token']) || $_SESSION['api_token'] !== $token) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        return true;
    }

    /**
     * GET /api/vehicles - Get all vehicles
     */
    public function vehicles() {
        $this->verifyToken();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $vehicles = $this->vehicleModel->getAllVehicles();
            $this->jsonResponse(['success' => true, 'vehicles' => $vehicles]);
        } else {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
    }

    /**
     * GET /api/vehicle/{id} - Get vehicle by ID
     */
    public function vehicle($id) {
        $this->verifyToken();

        $vehicle = $this->vehicleModel->getVehicleById($id);

        if ($vehicle) {
            $this->jsonResponse(['success' => true, 'vehicle' => $vehicle]);
        } else {
            $this->jsonResponse(['error' => 'Vehicle not found'], 404);
        }
    }

    /**
     * GET /api/tracking/positions - Get latest GPS positions
     */
    public function positions() {
        $this->verifyToken();

        $positions = $this->trackingModel->getLatestPositions();
        $this->jsonResponse(['success' => true, 'positions' => $positions]);
    }

    /**
     * POST /api/tracking/position - Add GPS position
     */
    public function position() {
        $this->verifyToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['vehicle_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
            $this->jsonResponse(['error' => 'vehicle_id, latitude, and longitude required'], 400);
        }

        if ($this->trackingModel->addPosition($data)) {
            $this->jsonResponse(['success' => true, 'message' => 'Position added successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to add position'], 500);
        }
    }

    /**
     * GET /api/orders - Get transport orders
     */
    public function orders() {
        $this->verifyToken();

        $orders = $this->transportModel->getAllOrders();
        $this->jsonResponse(['success' => true, 'orders' => $orders]);
    }

    /**
     * GET /api/order/{id} - Get order by ID
     */
    public function order($id) {
        $this->verifyToken();

        $order = $this->transportModel->getOrderById($id);

        if ($order) {
            $this->jsonResponse(['success' => true, 'order' => $order]);
        } else {
            $this->jsonResponse(['error' => 'Order not found'], 404);
        }
    }

    /**
     * PUT /api/order/{id}/status - Update order status
     */
    public function updateOrderStatus($id) {
        $this->verifyToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['status'])) {
            $this->jsonResponse(['error' => 'Status required'], 400);
        }

        if ($this->transportModel->updateOrderStatus($id, $data['status'])) {
            $this->jsonResponse(['success' => true, 'message' => 'Order status updated']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update order status'], 500);
        }
    }

    /**
     * GET /api/maintenance/work-orders - Get work orders
     */
    public function workOrders() {
        $this->verifyToken();

        $workOrders = $this->maintenanceModel->getAllWorkOrders();
        $this->jsonResponse(['success' => true, 'work_orders' => $workOrders]);
    }

    /**
     * GET /api/maintenance/due - Get due maintenances
     */
    public function dueMaintenances() {
        $this->verifyToken();

        $dueMaintenances = $this->maintenanceModel->getDueMaintenances();
        $this->jsonResponse(['success' => true, 'due_maintenances' => $dueMaintenances]);
    }

    /**
     * GET /api/alerts - Get all alerts
     */
    public function alerts() {
        $this->verifyToken();

        $speedAlerts = $this->trackingModel->getSpeedAlerts(50);
        $geofenceAlerts = $this->trackingModel->getGeofenceAlerts(50);

        $this->jsonResponse([
            'success' => true,
            'speed_alerts' => $speedAlerts,
            'geofence_alerts' => $geofenceAlerts
        ]);
    }

    /**
     * GET /api/stats - Get dashboard statistics
     */
    public function stats() {
        $this->verifyToken();

        $stats = [
            'total_vehicles' => $this->vehicleModel->countVehicles(),
            'active_vehicles' => $this->vehicleModel->countVehiclesByStatus('active'),
            'maintenance_vehicles' => $this->vehicleModel->countVehiclesByStatus('maintenance'),
            'total_users' => $this->userModel->countUsers()
        ];

        $this->jsonResponse(['success' => true, 'stats' => $stats]);
    }

    /**
     * Driver API Endpoints
     */

    /**
     * GET /api/driver/profile - Get driver profile
     */
    public function driverProfile() {
        $this->verifyToken();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        $user = $this->userModel->findById($userId);
        unset($user['password']); // Remove password from response

        $this->jsonResponse(['success' => true, 'user' => $user]);
    }

    /**
     * GET /api/driver/orders - Get driver's orders
     */
    public function driverOrders() {
        $this->verifyToken();

        $userId = $_SESSION['user_id'] ?? null;
        $status = $_GET['status'] ?? null;

        $orders = $this->transportModel->getOrdersByDriver($userId, $status);
        $this->jsonResponse(['success' => true, 'orders' => $orders]);
    }

    /**
     * GET /api/driver/orders/{id} - Get specific order details
     */
    public function driverOrderDetail($id) {
        $this->verifyToken();

        $order = $this->transportModel->getOrderById($id);
        if (!$order) {
            $this->jsonResponse(['error' => 'Order not found'], 404);
        }

        $this->jsonResponse(['success' => true, 'order' => $order]);
    }

    /**
     * GET /api/driver/current-trip - Get driver's current active trip
     */
    public function driverCurrentTrip() {
        $this->verifyToken();

        $userId = $_SESSION['user_id'] ?? null;
        $currentTrip = $this->transportModel->getCurrentTripByDriver($userId);

        if ($currentTrip) {
            $this->jsonResponse(['success' => true, 'trip' => $currentTrip]);
        } else {
            $this->jsonResponse(['success' => true, 'trip' => null]);
        }
    }

    /**
     * POST /api/driver/location - Update driver location
     */
    public function driverLocation() {
        $this->verifyToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['latitude']) || !isset($data['longitude'])) {
            $this->jsonResponse(['error' => 'Latitude and longitude required'], 400);
        }

        $userId = $_SESSION['user_id'] ?? null;
        $vehicleId = $data['vehicle_id'] ?? null;
        $orderId = $data['order_id'] ?? null;

        $positionData = [
            'device_id' => $vehicleId,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'speed' => $data['speed'] ?? 0,
            'heading' => $data['heading'] ?? 0,
            'altitude' => $data['altitude'] ?? 0,
            'accuracy' => $data['accuracy'] ?? 0
        ];

        if ($this->trackingModel->addPosition($positionData)) {
            $this->jsonResponse(['success' => true, 'message' => 'Location updated']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update location'], 500);
        }
    }

    /**
     * GET /api/driver/stats - Get driver statistics
     */
    public function driverStats() {
        $this->verifyToken();

        $userId = $_SESSION['user_id'] ?? null;

        // Get driver stats
        $stats = [
            'active_orders' => $this->transportModel->countOrdersByDriver($userId, 'in_transit'),
            'completed_today' => $this->transportModel->countOrdersByDriverToday($userId, 'delivered'),
            'distance_today' => $this->transportModel->getTotalDistanceToday($userId),
            'hours_today' => $this->transportModel->getTotalHoursToday($userId),
            'total_trips' => $this->transportModel->countOrdersByDriver($userId, null),
            'total_km' => $this->transportModel->getTotalDistance($userId),
            'rating' => 5.0 // Placeholder
        ];

        $this->jsonResponse(['success' => true, 'stats' => $stats]);
    }

    /**
     * PUT /api/driver/orders/{id}/status - Update order status (driver)
     */
    public function driverUpdateOrderStatus($id) {
        $this->verifyToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['status'])) {
            $this->jsonResponse(['error' => 'Status required'], 400);
        }

        // Verify driver owns this order
        $userId = $_SESSION['user_id'] ?? null;
        $order = $this->transportModel->getOrderById($id);

        if (!$order || $order['driver_id'] != $userId) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
        }

        if ($this->transportModel->updateOrderStatus($id, $data['status'])) {
            $this->jsonResponse(['success' => true, 'message' => 'Order status updated']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update order status'], 500);
        }
    }

    /**
     * GET /api/driver/notifications - Get driver notifications
     */
    public function driverNotifications() {
        $this->verifyToken();

        $userId = $_SESSION['user_id'] ?? null;

        // Get unread notifications
        $notifications = [
            [
                'id' => 1,
                'title' => 'Nouvelle mission assignée',
                'message' => 'Une nouvelle mission vous a été assignée',
                'type' => 'order',
                'created_at' => date('Y-m-d H:i:s'),
                'read' => false
            ]
        ];

        $this->jsonResponse(['success' => true, 'notifications' => $notifications]);
    }

    /**
     * Default route - API documentation
     */
    public function index() {
        $this->jsonResponse([
            'name' => 'DigiParc Fleet Management API',
            'version' => '1.0.0',
            'endpoints' => [
                'POST /api/login' => 'Login and get API token',
                'GET /api/vehicles' => 'Get all vehicles',
                'GET /api/vehicle/{id}' => 'Get vehicle by ID',
                'GET /api/tracking/positions' => 'Get latest GPS positions',
                'POST /api/tracking/position' => 'Add GPS position',
                'GET /api/orders' => 'Get transport orders',
                'GET /api/order/{id}' => 'Get order by ID',
                'PUT /api/order/{id}/status' => 'Update order status',
                'GET /api/maintenance/work-orders' => 'Get work orders',
                'GET /api/maintenance/due' => 'Get due maintenances',
                'GET /api/alerts' => 'Get all alerts',
                'GET /api/stats' => 'Get dashboard statistics',
                'GET /api/driver/profile' => 'Get driver profile',
                'GET /api/driver/orders' => 'Get driver orders',
                'GET /api/driver/orders/{id}' => 'Get order details',
                'GET /api/driver/current-trip' => 'Get current trip',
                'POST /api/driver/location' => 'Update driver location',
                'GET /api/driver/stats' => 'Get driver statistics',
                'PUT /api/driver/orders/{id}/status' => 'Update order status',
                'GET /api/driver/notifications' => 'Get driver notifications'
            ],
            'authentication' => 'Bearer token in Authorization header'
        ]);
    }
}
