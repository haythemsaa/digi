<?php
/**
 * GPS Tracking Controller
 */

class Tracking extends Controller {

    private $trackingModel;
    private $vehicleModel;

    public function __construct() {
        $this->trackingModel = $this->model('Tracking');
        $this->vehicleModel = $this->model('Vehicle');
    }

    /**
     * Real-time tracking map
     */
    public function index() {
        $positions = $this->trackingModel->getLatestPositions();
        $geofences = $this->trackingModel->getAllGeofences();

        $data = [
            'positions' => $positions,
            'geofences' => $geofences,
            'active_menu' => 'tracking',
            'page_title' => 'GPS Tracking - Real-time'
        ];

        $this->view('tracking/index', $data);
    }

    /**
     * Vehicle history/playback
     */
    public function history($vehicleId = null) {
        if (!$vehicleId) {
            $this->redirect('tracking');
        }

        $vehicle = $this->vehicleModel->getVehicleById($vehicleId);

        if (!$vehicle) {
            $_SESSION['error'] = 'Vehicle not found';
            $this->redirect('tracking');
        }

        // Default: last 24 hours
        $endDate = date('Y-m-d H:i:s');
        $startDate = date('Y-m-d H:i:s', strtotime('-24 hours'));

        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $startDate = $_GET['start_date'] . ' 00:00:00';
            $endDate = $_GET['end_date'] . ' 23:59:59';
        }

        $history = $this->trackingModel->getVehicleHistory($vehicleId, $startDate, $endDate);

        $data = [
            'vehicle' => $vehicle,
            'history' => $history,
            'start_date' => substr($startDate, 0, 10),
            'end_date' => substr($endDate, 0, 10),
            'active_menu' => 'tracking',
            'page_title' => 'GPS History - ' . $vehicle['registration_number']
        ];

        $this->view('tracking/history', $data);
    }

    /**
     * Geofences management
     */
    public function geofences() {
        $geofences = $this->trackingModel->getAllGeofences();

        $data = [
            'geofences' => $geofences,
            'active_menu' => 'tracking',
            'page_title' => 'Geofencing'
        ];

        $this->view('tracking/geofences', $data);
    }

    /**
     * Alerts (speed, geofence)
     */
    public function alerts() {
        $speedAlerts = $this->trackingModel->getSpeedAlerts(100);
        $geofenceAlerts = $this->trackingModel->getGeofenceAlerts(100);

        $data = [
            'speed_alerts' => $speedAlerts,
            'geofence_alerts' => $geofenceAlerts,
            'active_menu' => 'tracking',
            'page_title' => 'GPS Alerts'
        ];

        $this->view('tracking/alerts', $data);
    }

    /**
     * Active trips
     */
    public function trips() {
        $activeTrips = $this->trackingModel->getActiveTrips();

        $data = [
            'trips' => $activeTrips,
            'active_menu' => 'tracking',
            'page_title' => 'Active Trips'
        ];

        $this->view('tracking/trips', $data);
    }

    /**
     * API: Get latest positions (AJAX)
     */
    public function getPositions() {
        $positions = $this->trackingModel->getLatestPositions();
        $this->jsonResponse(['success' => true, 'positions' => $positions]);
    }

    /**
     * API: Add position (from GPS device or mobile app)
     */
    public function addPosition() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Validate required fields
        if (!isset($data['vehicle_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
            $this->jsonResponse(['error' => 'Missing required fields'], 400);
        }

        if ($this->trackingModel->addPosition($data)) {
            $this->jsonResponse(['success' => true, 'message' => 'Position added']);
        } else {
            $this->jsonResponse(['error' => 'Failed to add position'], 500);
        }
    }
}
