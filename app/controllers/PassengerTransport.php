<?php
/**
 * Passenger Transport Controller
 * Handles taxi and bus transportation management
 */

class PassengerTransport extends Controller {
    private $transportModel;
    private $vehicleModel;

    public function __construct() {
        $this->transportModel = $this->model('PassengerTransport');
        $this->vehicleModel = $this->model('Vehicle');
    }

    /**
     * Dashboard / Index
     */
    public function index() {
        $data = [
            'page_title' => 'Transport de Voyageurs - Tableau de Bord',
            'active_menu' => 'passenger_transport',
            'stats' => $this->transportModel->getDashboardStats(),
            'recent_bookings' => $this->transportModel->getRecentBookings(10)
        ];

        $this->view('passenger_transport/index', $data);
    }

    // ========================================================================
    // PASSENGER MANAGEMENT
    // ========================================================================

    /**
     * List all passengers
     */
    public function passengers() {
        $filters = [];
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        $data = [
            'page_title' => 'Gestion des Passagers',
            'active_menu' => 'passenger_transport',
            'passengers' => $this->transportModel->getAllPassengers($filters)
        ];

        $this->view('passenger_transport/passengers', $data);
    }

    /**
     * Add new passenger form
     */
    public function addPassenger() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $passengerData = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'phone' => trim($_POST['phone']),
                'email' => trim($_POST['email']),
                'id_card_number' => trim($_POST['id_card_number']),
                'address' => trim($_POST['address']),
                'city' => trim($_POST['city']),
                'postal_code' => trim($_POST['postal_code']),
                'date_of_birth' => $_POST['date_of_birth'] ?: null,
                'gender' => $_POST['gender'] ?: null,
                'preferred_payment_method' => $_POST['preferred_payment_method'] ?? 'cash',
                'notes' => trim($_POST['notes'])
            ];

            $passengerId = $this->transportModel->createPassenger($passengerData);

            if ($passengerId) {
                $_SESSION['success'] = 'Passager créé avec succès';
                $this->redirect('passenger_transport/viewPassenger/' . $passengerId);
            } else {
                $_SESSION['error'] = 'Erreur lors de la création du passager';
            }
        }

        $data = [
            'page_title' => 'Ajouter un Passager',
            'active_menu' => 'passenger_transport'
        ];

        $this->view('passenger_transport/add_passenger', $data);
    }

    /**
     * View passenger details
     */
    public function viewPassenger($id) {
        $passenger = $this->transportModel->getPassengerById($id);

        if (!$passenger) {
            $_SESSION['error'] = 'Passager introuvable';
            $this->redirect('passenger_transport/passengers');
        }

        // Get passenger's booking history
        $bookings = $this->transportModel->getAllBookings(['passenger_id' => $id]);

        $data = [
            'page_title' => 'Détails Passager',
            'active_menu' => 'passenger_transport',
            'passenger' => $passenger,
            'bookings' => $bookings
        ];

        $this->view('passenger_transport/view_passenger', $data);
    }

    /**
     * Edit passenger
     */
    public function editPassenger($id) {
        $passenger = $this->transportModel->getPassengerById($id);

        if (!$passenger) {
            $_SESSION['error'] = 'Passager introuvable';
            $this->redirect('passenger_transport/passengers');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $passengerData = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'phone' => trim($_POST['phone']),
                'email' => trim($_POST['email']),
                'id_card_number' => trim($_POST['id_card_number']),
                'address' => trim($_POST['address']),
                'city' => trim($_POST['city']),
                'postal_code' => trim($_POST['postal_code']),
                'date_of_birth' => $_POST['date_of_birth'] ?: null,
                'gender' => $_POST['gender'] ?: null,
                'preferred_payment_method' => $_POST['preferred_payment_method'] ?? 'cash',
                'notes' => trim($_POST['notes'])
            ];

            if ($this->transportModel->updatePassenger($id, $passengerData)) {
                $_SESSION['success'] = 'Passager mis à jour avec succès';
                $this->redirect('passenger_transport/viewPassenger/' . $id);
            } else {
                $_SESSION['error'] = 'Erreur lors de la mise à jour';
            }
        }

        $data = [
            'page_title' => 'Éditer Passager',
            'active_menu' => 'passenger_transport',
            'passenger' => $passenger
        ];

        $this->view('passenger_transport/edit_passenger', $data);
    }

    // ========================================================================
    // RIDE BOOKINGS
    // ========================================================================

    /**
     * List all bookings
     */
    public function bookings() {
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['booking_type'])) {
            $filters['booking_type'] = $_GET['booking_type'];
        }
        if (isset($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (isset($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        $data = [
            'page_title' => 'Réservations',
            'active_menu' => 'passenger_transport',
            'bookings' => $this->transportModel->getAllBookings($filters),
            'filters' => $filters
        ];

        $this->view('passenger_transport/bookings', $data);
    }

    /**
     * Create new booking
     */
    public function createBooking() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Check if passenger exists, create if not
            $passengerId = null;
            if (!empty($_POST['passenger_id'])) {
                $passengerId = $_POST['passenger_id'];
            } elseif (!empty($_POST['passenger_phone'])) {
                // Try to find by phone
                $passenger = $this->transportModel->getPassengerByPhone($_POST['passenger_phone']);
                if ($passenger) {
                    $passengerId = $passenger['id'];
                } else {
                    // Create quick passenger
                    $passengerData = [
                        'first_name' => $_POST['passenger_first_name'] ?? 'Guest',
                        'last_name' => $_POST['passenger_last_name'] ?? '',
                        'phone' => $_POST['passenger_phone']
                    ];
                    $passengerId = $this->transportModel->createPassenger($passengerData);
                }
            }

            if (!$passengerId) {
                $_SESSION['error'] = 'Passager requis';
                $this->redirect('passenger_transport/createBooking');
                return;
            }

            $bookingData = [
                'passenger_id' => $passengerId,
                'booking_type' => $_POST['booking_type'] ?? 'taxi',
                'ride_type' => $_POST['ride_type'] ?? 'immediate',
                'pickup_address' => $_POST['pickup_address'],
                'pickup_latitude' => $_POST['pickup_latitude'] ?: null,
                'pickup_longitude' => $_POST['pickup_longitude'] ?: null,
                'dropoff_address' => $_POST['dropoff_address'],
                'dropoff_latitude' => $_POST['dropoff_latitude'] ?: null,
                'dropoff_longitude' => $_POST['dropoff_longitude'] ?: null,
                'scheduled_pickup_time' => $_POST['scheduled_pickup_time'] ?: null,
                'passenger_count' => $_POST['passenger_count'] ?? 1,
                'comfort_class' => $_POST['comfort_class'] ?? 'standard',
                'special_requirements' => $_POST['special_requirements'] ?: null,
                'estimated_distance_km' => $_POST['estimated_distance_km'] ?? 0,
                'estimated_duration_minutes' => $_POST['estimated_duration_minutes'] ?? 0,
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'notes' => $_POST['notes'] ?: null
            ];

            $bookingId = $this->transportModel->createBooking($bookingData);

            if ($bookingId) {
                $_SESSION['success'] = 'Réservation créée avec succès';
                $this->redirect('passenger_transport/viewBooking/' . $bookingId);
            } else {
                $_SESSION['error'] = 'Erreur lors de la création de la réservation';
            }
        }

        $data = [
            'page_title' => 'Nouvelle Réservation',
            'active_menu' => 'passenger_transport',
            'passengers' => $this->transportModel->getAllPassengers(['is_active' => 1])
        ];

        $this->view('passenger_transport/create_booking', $data);
    }

    /**
     * View booking details
     */
    public function viewBooking($id) {
        $booking = $this->transportModel->getBookingById($id);

        if (!$booking) {
            $_SESSION['error'] = 'Réservation introuvable';
            $this->redirect('passenger_transport/bookings');
        }

        $data = [
            'page_title' => 'Détails Réservation',
            'active_menu' => 'passenger_transport',
            'booking' => $booking
        ];

        $this->view('passenger_transport/view_booking', $data);
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $status = $_POST['status'];
            $additionalData = [];

            if ($status == 'completed') {
                $additionalData['actual_distance_km'] = $_POST['actual_distance_km'] ?? 0;
                $additionalData['actual_fare'] = $_POST['actual_fare'] ?? 0;
            } elseif ($status == 'cancelled') {
                $additionalData['cancelled_by'] = $_POST['cancelled_by'] ?? 'admin';
                $additionalData['cancellation_reason'] = $_POST['cancellation_reason'] ?? '';
            }

            if ($this->transportModel->updateBookingStatus($id, $status, $additionalData)) {
                $_SESSION['success'] = 'Statut mis à jour avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de la mise à jour du statut';
            }
        }

        $this->redirect('passenger_transport/viewBooking/' . $id);
    }

    // ========================================================================
    // BUS ROUTES
    // ========================================================================

    /**
     * List all bus routes
     */
    public function busRoutes() {
        $data = [
            'page_title' => 'Lignes de Bus',
            'active_menu' => 'passenger_transport',
            'routes' => $this->transportModel->getAllBusRoutes()
        ];

        $this->view('passenger_transport/bus_routes', $data);
    }

    /**
     * View bus route details
     */
    public function viewBusRoute($id) {
        $route = $this->transportModel->getBusRouteById($id);

        if (!$route) {
            $_SESSION['error'] = 'Ligne de bus introuvable';
            $this->redirect('passenger_transport/busRoutes');
        }

        $data = [
            'page_title' => 'Détails Ligne de Bus',
            'active_menu' => 'passenger_transport',
            'route' => $route
        ];

        $this->view('passenger_transport/view_bus_route', $data);
    }

    /**
     * Create bus route
     */
    public function createBusRoute() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $routeData = [
                'route_number' => trim($_POST['route_number']),
                'route_name' => trim($_POST['route_name']),
                'description' => trim($_POST['description']),
                'start_point' => trim($_POST['start_point']),
                'end_point' => trim($_POST['end_point']),
                'route_type' => $_POST['route_type'] ?? 'urban',
                'total_distance_km' => $_POST['total_distance_km'] ?? 0,
                'estimated_duration_minutes' => $_POST['estimated_duration_minutes'] ?? 0,
                'base_fare' => $_POST['base_fare'] ?? 0,
                'fare_per_km' => $_POST['fare_per_km'] ?? 0,
                'is_circular' => isset($_POST['is_circular']) ? 1 : 0,
                'first_departure' => $_POST['first_departure'] ?? '06:00',
                'last_departure' => $_POST['last_departure'] ?? '22:00',
                'frequency_minutes' => $_POST['frequency_minutes'] ?? 30,
                'color_code' => $_POST['color_code'] ?? '#007bff'
            ];

            $routeId = $this->transportModel->createBusRoute($routeData);

            if ($routeId) {
                $_SESSION['success'] = 'Ligne de bus créée avec succès';
                $this->redirect('passenger_transport/viewBusRoute/' . $routeId);
            } else {
                $_SESSION['error'] = 'Erreur lors de la création de la ligne';
            }
        }

        $data = [
            'page_title' => 'Créer Ligne de Bus',
            'active_menu' => 'passenger_transport'
        ];

        $this->view('passenger_transport/create_bus_route', $data);
    }

    /**
     * Bus stops
     */
    public function busStops() {
        $data = [
            'page_title' => 'Arrêts de Bus',
            'active_menu' => 'passenger_transport',
            'stops' => $this->transportModel->getAllBusStops()
        ];

        $this->view('passenger_transport/bus_stops', $data);
    }

    /**
     * Create bus stop
     */
    public function createBusStop() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $stopData = [
                'stop_name' => trim($_POST['stop_name']),
                'address' => trim($_POST['address']),
                'latitude' => $_POST['latitude'],
                'longitude' => $_POST['longitude'],
                'zone' => $_POST['zone'] ?? null,
                'has_shelter' => isset($_POST['has_shelter']) ? 1 : 0,
                'has_bench' => isset($_POST['has_bench']) ? 1 : 0,
                'has_lighting' => isset($_POST['has_lighting']) ? 1 : 0,
                'is_accessible' => isset($_POST['is_accessible']) ? 1 : 0,
                'nearby_landmarks' => trim($_POST['nearby_landmarks'])
            ];

            $stopId = $this->transportModel->createBusStop($stopData);

            if ($stopId) {
                $_SESSION['success'] = 'Arrêt de bus créé avec succès';
                $this->redirect('passenger_transport/busStops');
            } else {
                $_SESSION['error'] = 'Erreur lors de la création de l\'arrêt';
            }
        }

        $data = [
            'page_title' => 'Créer Arrêt de Bus',
            'active_menu' => 'passenger_transport'
        ];

        $this->view('passenger_transport/create_bus_stop', $data);
    }

    // ========================================================================
    // ANALYTICS
    // ========================================================================

    /**
     * Analytics dashboard
     */
    public function analytics() {
        $data = [
            'page_title' => 'Analytiques Transport',
            'active_menu' => 'passenger_transport',
            'stats' => $this->transportModel->getDashboardStats()
        ];

        $this->view('passenger_transport/analytics', $data);
    }

    // ========================================================================
    // API ENDPOINTS FOR MOBILE
    // ========================================================================

    /**
     * API: Get available drivers near location
     */
    public function apiGetNearbyDrivers() {
        header('Content-Type: application/json');

        $lat = $_GET['lat'] ?? null;
        $lng = $_GET['lng'] ?? null;
        $radius = $_GET['radius'] ?? 10; // km

        if (!$lat || !$lng) {
            echo json_encode(['success' => false, 'error' => 'Coordinates required']);
            exit;
        }

        // Implementation would query nearby drivers
        echo json_encode([
            'success' => true,
            'drivers' => []
        ]);
        exit;
    }

    /**
     * API: Create booking from mobile
     */
    public function apiCreateBooking() {
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Validate and create booking
        // ...

        echo json_encode([
            'success' => true,
            'booking_id' => 123,
            'booking_number' => 'BOOK123456'
        ]);
        exit;
    }
}
