<?php
/**
 * Passenger Transport Model - Multi-tenant enabled
 * Handles all business logic for taxi and bus transportation
 */

class PassengerTransport extends Model {
    private $companyId;

    public function __construct() {
        parent::__construct();
        $this->companyId = getCurrentCompanyId();

        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    private function getCompanyFilter($tableAlias = '') {
        if (isSuperAdmin()) {
            return '1=1';
        }
        $prefix = $tableAlias ? "{$tableAlias}." : '';
        return "{$prefix}company_id = :company_id";
    }

    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    // ========================================================================
    // PASSENGER MANAGEMENT
    // ========================================================================

    /**
     * Get all passengers with optional filtering
     */
    public function getAllPassengers($filters = []) {
        $companyFilter = $this->getCompanyFilter('');
        $sql = "SELECT * FROM passengers WHERE {$companyFilter}";

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR phone LIKE :search OR email LIKE :search OR customer_code LIKE :search)";
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = :is_active";
        }

        $sql .= " ORDER BY created_at DESC";

        $this->db->query($sql);
        $this->bindCompanyId();

        if (isset($filters['search']) && !empty($filters['search'])) {
            $this->db->bind(':search', $search);
        }
        if (isset($filters['is_active'])) {
            $this->db->bind(':is_active', $filters['is_active']);
        }

        return $this->db->resultSet();
    }

    /**
     * Get passenger by ID
     */
    public function getPassengerById($id) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("SELECT * FROM passengers WHERE id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->single();
    }

    /**
     * Get passenger by phone
     */
    public function getPassengerByPhone($phone) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("SELECT * FROM passengers WHERE phone = :phone AND {$companyFilter}");
        $this->db->bind(':phone', $phone);
        $this->bindCompanyId();
        return $this->db->single();
    }

    /**
     * Create new passenger
     */
    public function createPassenger($data) {
        // Generate customer code
        $customerCode = 'PASS' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->db->query("INSERT INTO passengers
            (company_id, customer_code, first_name, last_name, phone, email, id_card_number,
             address, city, postal_code, date_of_birth, gender, preferred_payment_method, notes)
            VALUES
            (:company_id, :customer_code, :first_name, :last_name, :phone, :email, :id_card_number,
             :address, :city, :postal_code, :date_of_birth, :gender, :payment_method, :notes)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':customer_code', $customerCode);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':id_card_number', $data['id_card_number'] ?? null);
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':city', $data['city'] ?? null);
        $this->db->bind(':postal_code', $data['postal_code'] ?? null);
        $this->db->bind(':date_of_birth', $data['date_of_birth'] ?? null);
        $this->db->bind(':gender', $data['gender'] ?? null);
        $this->db->bind(':payment_method', $data['preferred_payment_method'] ?? 'cash');
        $this->db->bind(':notes', $data['notes'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update passenger
     */
    public function updatePassenger($id, $data) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE passengers SET
            first_name = :first_name,
            last_name = :last_name,
            phone = :phone,
            email = :email,
            id_card_number = :id_card_number,
            address = :address,
            city = :city,
            postal_code = :postal_code,
            date_of_birth = :date_of_birth,
            gender = :gender,
            preferred_payment_method = :payment_method,
            notes = :notes
            WHERE id = :id AND {$companyFilter}");

        $this->db->bind(':id', $id);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':id_card_number', $data['id_card_number'] ?? null);
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':city', $data['city'] ?? null);
        $this->db->bind(':postal_code', $data['postal_code'] ?? null);
        $this->db->bind(':date_of_birth', $data['date_of_birth'] ?? null);
        $this->db->bind(':gender', $data['gender'] ?? null);
        $this->db->bind(':payment_method', $data['preferred_payment_method'] ?? 'cash');
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->bindCompanyId();

        return $this->db->execute();
    }

    // ========================================================================
    // RIDE BOOKINGS
    // ========================================================================

    /**
     * Create a new ride booking
     */
    public function createBooking($data) {
        // Generate booking number
        $bookingNumber = 'BOOK' . date('YmdHis') . rand(100, 999);

        // Calculate estimated fare
        $estimatedFare = $this->calculateFare($data);

        $this->db->query("INSERT INTO ride_bookings
            (company_id, booking_number, passenger_id, booking_type, ride_type, status,
             pickup_address, pickup_latitude, pickup_longitude,
             dropoff_address, dropoff_latitude, dropoff_longitude,
             scheduled_pickup_time, passenger_count, comfort_class,
             special_requirements, estimated_distance_km, estimated_duration_minutes,
             estimated_fare, final_amount, payment_method, notes)
            VALUES
            (:company_id, :booking_number, :passenger_id, :booking_type, :ride_type, :status,
             :pickup_address, :pickup_lat, :pickup_lng,
             :dropoff_address, :dropoff_lat, :dropoff_lng,
             :scheduled_time, :passenger_count, :comfort_class,
             :special_req, :est_distance, :est_duration,
             :estimated_fare, :final_amount, :payment_method, :notes)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':booking_number', $bookingNumber);
        $this->db->bind(':passenger_id', $data['passenger_id']);
        $this->db->bind(':booking_type', $data['booking_type'] ?? 'taxi');
        $this->db->bind(':ride_type', $data['ride_type'] ?? 'immediate');
        $this->db->bind(':status', 'pending');
        $this->db->bind(':pickup_address', $data['pickup_address']);
        $this->db->bind(':pickup_lat', $data['pickup_latitude'] ?? null);
        $this->db->bind(':pickup_lng', $data['pickup_longitude'] ?? null);
        $this->db->bind(':dropoff_address', $data['dropoff_address']);
        $this->db->bind(':dropoff_lat', $data['dropoff_latitude'] ?? null);
        $this->db->bind(':dropoff_lng', $data['dropoff_longitude'] ?? null);
        $this->db->bind(':scheduled_time', $data['scheduled_pickup_time'] ?? null);
        $this->db->bind(':passenger_count', $data['passenger_count'] ?? 1);
        $this->db->bind(':comfort_class', $data['comfort_class'] ?? 'standard');
        $this->db->bind(':special_req', $data['special_requirements'] ?? null);
        $this->db->bind(':est_distance', $data['estimated_distance_km'] ?? 0);
        $this->db->bind(':est_duration', $data['estimated_duration_minutes'] ?? 0);
        $this->db->bind(':estimated_fare', $estimatedFare);
        $this->db->bind(':final_amount', $estimatedFare);
        $this->db->bind(':payment_method', $data['payment_method'] ?? 'cash');
        $this->db->bind(':notes', $data['notes'] ?? null);

        if ($this->db->execute()) {
            $bookingId = $this->db->lastInsertId();

            // Auto-assign driver if immediate ride
            if ($data['ride_type'] == 'immediate') {
                $this->autoAssignDriver($bookingId, $data);
            }

            return $bookingId;
        }
        return false;
    }

    /**
     * Calculate fare based on distance, time, and zone
     */
    private function calculateFare($data) {
        $baseFare = 3.00; // Default base fare
        $pricePerKm = 0.80;
        $pricePerMinute = 0.10;

        // Get pricing zone if available
        if (isset($data['zone_name'])) {
            $zone = $this->getPricingZone($data['zone_name']);
            if ($zone) {
                $baseFare = $zone['base_fare'];
                $pricePerKm = $zone['price_per_km'];
                $pricePerMinute = $zone['price_per_minute'];
            }
        }

        $distance = floatval($data['estimated_distance_km'] ?? 0);
        $duration = floatval($data['estimated_duration_minutes'] ?? 0);

        $fare = $baseFare + ($distance * $pricePerKm) + ($duration * $pricePerMinute);

        // Apply surge multiplier if set
        if (isset($data['surge_multiplier']) && $data['surge_multiplier'] > 1) {
            $fare *= floatval($data['surge_multiplier']);
        }

        // Apply comfort class multiplier
        $comfortMultipliers = [
            'economy' => 0.8,
            'standard' => 1.0,
            'comfort' => 1.3,
            'premium' => 1.6,
            'luxury' => 2.0
        ];

        $comfortClass = $data['comfort_class'] ?? 'standard';
        if (isset($comfortMultipliers[$comfortClass])) {
            $fare *= $comfortMultipliers[$comfortClass];
        }

        // Check for night surcharge
        $currentHour = date('H');
        if ($currentHour >= 22 || $currentHour < 6) {
            $fare *= 1.25; // 25% night surcharge
        }

        return round($fare, 2);
    }

    /**
     * Auto-assign nearest available driver
     */
    private function autoAssignDriver($bookingId, $bookingData) {
        $companyFilter = $this->getCompanyFilter('td');
        // Find available drivers within radius
        $this->db->query("SELECT td.*,
            (6371 * acos(cos(radians(:pickup_lat)) * cos(radians(td.current_latitude))
            * cos(radians(td.current_longitude) - radians(:pickup_lng))
            + sin(radians(:pickup_lat)) * sin(radians(td.current_latitude)))) AS distance_km
            FROM transport_drivers td
            WHERE td.current_status = 'available'
            AND td.is_active = 1
            AND td.current_latitude IS NOT NULL
            AND td.current_longitude IS NOT NULL
            AND {$companyFilter}
            HAVING distance_km < 10
            ORDER BY distance_km ASC
            LIMIT 1");

        $this->db->bind(':pickup_lat', $bookingData['pickup_latitude']);
        $this->db->bind(':pickup_lng', $bookingData['pickup_longitude']);
        $this->bindCompanyId();

        $driver = $this->db->single();

        if ($driver) {
            // Assign driver
            $companyFilter2 = $this->getCompanyFilter('');
            $this->db->query("UPDATE ride_bookings SET
                driver_id = :driver_id,
                vehicle_id = :vehicle_id,
                status = 'driver_assigned'
                WHERE id = :booking_id AND {$companyFilter2}");

            $this->db->bind(':driver_id', $driver['id']);
            $this->db->bind(':vehicle_id', $driver['current_vehicle_id']);
            $this->db->bind(':booking_id', $bookingId);
            $this->bindCompanyId();
            $this->db->execute();

            // Update driver status
            $this->db->query("UPDATE transport_drivers SET
                current_status = 'on_trip'
                WHERE id = :driver_id AND {$companyFilter2}");
            $this->db->bind(':driver_id', $driver['id']);
            $this->bindCompanyId();
            $this->db->execute();

            return $driver['id'];
        }

        return false;
    }

    /**
     * Get booking by ID with all details
     */
    public function getBookingById($id) {
        $companyFilter = $this->getCompanyFilter('rb');
        $this->db->query("SELECT rb.*,
            p.first_name AS passenger_first_name,
            p.last_name AS passenger_last_name,
            p.phone AS passenger_phone,
            p.email AS passenger_email,
            u.first_name AS driver_first_name,
            u.last_name AS driver_last_name,
            tv.license_plate,
            tv.passenger_capacity,
            tv.comfort_class AS vehicle_class
            FROM ride_bookings rb
            LEFT JOIN passengers p ON rb.passenger_id = p.id
            LEFT JOIN transport_drivers td ON rb.driver_id = td.id
            LEFT JOIN users u ON td.user_id = u.id
            LEFT JOIN transport_vehicles tv ON rb.vehicle_id = tv.id
            WHERE rb.id = :id AND {$companyFilter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->single();
    }

    /**
     * Get all bookings with filters
     */
    public function getAllBookings($filters = []) {
        $companyFilter = $this->getCompanyFilter('rb');
        $sql = "SELECT rb.*,
            p.first_name AS passenger_first_name,
            p.last_name AS passenger_last_name,
            u.first_name AS driver_first_name,
            u.last_name AS driver_last_name
            FROM ride_bookings rb
            LEFT JOIN passengers p ON rb.passenger_id = p.id
            LEFT JOIN transport_drivers td ON rb.driver_id = td.id
            LEFT JOIN users u ON td.user_id = u.id
            WHERE {$companyFilter}";

        if (isset($filters['status'])) {
            $sql .= " AND rb.status = :status";
        }

        if (isset($filters['booking_type'])) {
            $sql .= " AND rb.booking_type = :booking_type";
        }

        if (isset($filters['date_from'])) {
            $sql .= " AND DATE(rb.created_at) >= :date_from";
        }

        if (isset($filters['date_to'])) {
            $sql .= " AND DATE(rb.created_at) <= :date_to";
        }

        $sql .= " ORDER BY rb.created_at DESC LIMIT 100";

        $this->db->query($sql);
        $this->bindCompanyId();

        if (isset($filters['status'])) {
            $this->db->bind(':status', $filters['status']);
        }
        if (isset($filters['booking_type'])) {
            $this->db->bind(':booking_type', $filters['booking_type']);
        }
        if (isset($filters['date_from'])) {
            $this->db->bind(':date_from', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $this->db->bind(':date_to', $filters['date_to']);
        }

        return $this->db->resultSet();
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus($id, $status, $additionalData = []) {
        $sql = "UPDATE ride_bookings SET status = :status";

        if ($status == 'in_progress') {
            $sql .= ", actual_pickup_time = NOW()";
        } elseif ($status == 'completed') {
            $sql .= ", actual_dropoff_time = NOW()";
            if (isset($additionalData['actual_distance_km'])) {
                $sql .= ", actual_distance_km = :actual_distance";
            }
            if (isset($additionalData['actual_fare'])) {
                $sql .= ", actual_fare = :actual_fare, final_amount = :actual_fare";
            }
        } elseif ($status == 'cancelled') {
            if (isset($additionalData['cancelled_by'])) {
                $sql .= ", cancelled_by = :cancelled_by";
            }
            if (isset($additionalData['cancellation_reason'])) {
                $sql .= ", cancellation_reason = :cancel_reason";
            }
            $sql .= ", cancelled_at = NOW()";
        }

        $companyFilter = $this->getCompanyFilter('');
        $sql .= " WHERE id = :id AND {$companyFilter}";

        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        $this->bindCompanyId();

        if (isset($additionalData['actual_distance_km'])) {
            $this->db->bind(':actual_distance', $additionalData['actual_distance_km']);
        }
        if (isset($additionalData['actual_fare'])) {
            $this->db->bind(':actual_fare', $additionalData['actual_fare']);
        }
        if (isset($additionalData['cancelled_by'])) {
            $this->db->bind(':cancelled_by', $additionalData['cancelled_by']);
        }
        if (isset($additionalData['cancellation_reason'])) {
            $this->db->bind(':cancel_reason', $additionalData['cancellation_reason']);
        }

        $result = $this->db->execute();

        // Update passenger trip count on completion
        if ($status == 'completed' && $result) {
            $booking = $this->getBookingById($id);
            $companyFilter2 = $this->getCompanyFilter('');
            $this->db->query("UPDATE passengers SET total_trips = total_trips + 1 WHERE id = :passenger_id AND {$companyFilter2}");
            $this->db->bind(':passenger_id', $booking['passenger_id']);
            $this->bindCompanyId();
            $this->db->execute();

            // Update driver trip count
            if ($booking['driver_id']) {
                $this->db->query("UPDATE transport_drivers SET
                    total_trips = total_trips + 1,
                    total_distance_km = total_distance_km + :distance,
                    current_status = 'available'
                    WHERE id = :driver_id AND {$companyFilter2}");
                $this->db->bind(':distance', $additionalData['actual_distance_km'] ?? 0);
                $this->db->bind(':driver_id', $booking['driver_id']);
                $this->bindCompanyId();
                $this->db->execute();
            }
        }

        return $result;
    }

    // ========================================================================
    // BUS ROUTES & STOPS
    // ========================================================================

    /**
     * Get all bus routes
     */
    public function getAllBusRoutes($activeOnly = false) {
        $companyFilter = $this->getCompanyFilter('');
        $sql = "SELECT * FROM bus_routes WHERE {$companyFilter}";
        if ($activeOnly) {
            $sql .= " AND status = 'active'";
        }
        $sql .= " ORDER BY route_number ASC";

        $this->db->query($sql);
        $this->bindCompanyId();
        return $this->db->resultSet();
    }

    /**
     * Get bus route by ID with stops
     */
    public function getBusRouteById($id) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("SELECT * FROM bus_routes WHERE id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $route = $this->db->single();

        if ($route) {
            // Get stops
            $companyFilter2 = $this->getCompanyFilter('rs');
            $this->db->query("SELECT rs.*, bs.*,
                rs.stop_sequence, rs.distance_from_start_km, rs.estimated_minutes_from_start
                FROM route_stops rs
                JOIN bus_stops bs ON rs.stop_id = bs.id
                WHERE rs.route_id = :route_id AND {$companyFilter2}
                ORDER BY rs.stop_sequence ASC");
            $this->db->bind(':route_id', $id);
            $this->bindCompanyId();
            $route['stops'] = $this->db->resultSet();
        }

        return $route;
    }

    /**
     * Create bus route
     */
    public function createBusRoute($data) {
        $this->db->query("INSERT INTO bus_routes
            (company_id, route_number, route_name, description, start_point, end_point,
             route_type, total_distance_km, estimated_duration_minutes,
             base_fare, fare_per_km, is_circular, first_departure, last_departure,
             frequency_minutes, status, color_code)
            VALUES
            (:company_id, :route_number, :route_name, :description, :start_point, :end_point,
             :route_type, :total_distance, :duration,
             :base_fare, :fare_per_km, :is_circular, :first_dep, :last_dep,
             :frequency, :status, :color_code)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':route_number', $data['route_number']);
        $this->db->bind(':route_name', $data['route_name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':start_point', $data['start_point']);
        $this->db->bind(':end_point', $data['end_point']);
        $this->db->bind(':route_type', $data['route_type'] ?? 'urban');
        $this->db->bind(':total_distance', $data['total_distance_km'] ?? 0);
        $this->db->bind(':duration', $data['estimated_duration_minutes'] ?? 0);
        $this->db->bind(':base_fare', $data['base_fare'] ?? 0);
        $this->db->bind(':fare_per_km', $data['fare_per_km'] ?? 0);
        $this->db->bind(':is_circular', $data['is_circular'] ?? 0);
        $this->db->bind(':first_dep', $data['first_departure'] ?? '06:00');
        $this->db->bind(':last_dep', $data['last_departure'] ?? '22:00');
        $this->db->bind(':frequency', $data['frequency_minutes'] ?? 30);
        $this->db->bind(':status', 'active');
        $this->db->bind(':color_code', $data['color_code'] ?? '#007bff');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Get all bus stops
     */
    public function getAllBusStops() {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("SELECT * FROM bus_stops WHERE is_active = 1 AND {$companyFilter} ORDER BY stop_name ASC");
        $this->bindCompanyId();
        return $this->db->resultSet();
    }

    /**
     * Create bus stop
     */
    public function createBusStop($data) {
        $stopCode = 'STOP' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->db->query("INSERT INTO bus_stops
            (company_id, stop_code, stop_name, address, latitude, longitude, zone,
             has_shelter, has_bench, has_lighting, is_accessible, nearby_landmarks)
            VALUES
            (:company_id, :stop_code, :stop_name, :address, :lat, :lng, :zone,
             :has_shelter, :has_bench, :has_lighting, :is_accessible, :landmarks)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':stop_code', $stopCode);
        $this->db->bind(':stop_name', $data['stop_name']);
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':lat', $data['latitude']);
        $this->db->bind(':lng', $data['longitude']);
        $this->db->bind(':zone', $data['zone'] ?? null);
        $this->db->bind(':has_shelter', $data['has_shelter'] ?? 0);
        $this->db->bind(':has_bench', $data['has_bench'] ?? 0);
        $this->db->bind(':has_lighting', $data['has_lighting'] ?? 0);
        $this->db->bind(':is_accessible', $data['is_accessible'] ?? 0);
        $this->db->bind(':landmarks', $data['nearby_landmarks'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // ========================================================================
    // PRICING & PAYMENTS
    // ========================================================================

    /**
     * Get pricing zone by name
     */
    public function getPricingZone($zoneName) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("SELECT * FROM pricing_zones
            WHERE zone_name = :zone_name AND is_active = 1
            AND (effective_from IS NULL OR effective_from <= CURDATE())
            AND (effective_until IS NULL OR effective_until >= CURDATE())
            AND {$companyFilter}
            LIMIT 1");
        $this->db->bind(':zone_name', $zoneName);
        $this->bindCompanyId();
        return $this->db->single();
    }

    /**
     * Record payment
     */
    public function recordPayment($bookingId, $amount, $paymentMethod, $transactionId = null) {
        $paymentRef = 'PAY' . date('YmdHis') . rand(100, 999);

        $this->db->query("INSERT INTO transport_payments
            (company_id, booking_id, payment_reference, amount, payment_method, payment_status, transaction_id, paid_at)
            VALUES
            (:company_id, :booking_id, :payment_ref, :amount, :payment_method, 'completed', :transaction_id, NOW())");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':booking_id', $bookingId);
        $this->db->bind(':payment_ref', $paymentRef);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':payment_method', $paymentMethod);
        $this->db->bind(':transaction_id', $transactionId);

        if ($this->db->execute()) {
            // Update booking payment status
            $companyFilter = $this->getCompanyFilter('');
            $this->db->query("UPDATE ride_bookings SET payment_status = 'paid', paid_at = NOW() WHERE id = :booking_id AND {$companyFilter}");
            $this->db->bind(':booking_id', $bookingId);
            $this->bindCompanyId();
            $this->db->execute();

            return $this->db->lastInsertId();
        }
        return false;
    }

    // ========================================================================
    // STATISTICS & ANALYTICS
    // ========================================================================

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $companyFilter = $this->getCompanyFilter('');
        $stats = [];

        // Today's bookings
        $this->db->query("SELECT COUNT(*) as count FROM ride_bookings WHERE DATE(created_at) = CURDATE() AND {$companyFilter}");
        $this->bindCompanyId();
        $stats['today_bookings'] = $this->db->single()['count'];

        // Active rides
        $this->db->query("SELECT COUNT(*) as count FROM ride_bookings WHERE status IN ('driver_assigned', 'driver_arrived', 'in_progress') AND {$companyFilter}");
        $this->bindCompanyId();
        $stats['active_rides'] = $this->db->single()['count'];

        // Available drivers
        $this->db->query("SELECT COUNT(*) as count FROM transport_drivers WHERE current_status = 'available' AND is_active = 1 AND {$companyFilter}");
        $this->bindCompanyId();
        $stats['available_drivers'] = $this->db->single()['count'];

        // Total passengers
        $this->db->query("SELECT COUNT(*) as count FROM passengers WHERE is_active = 1 AND {$companyFilter}");
        $this->bindCompanyId();
        $stats['total_passengers'] = $this->db->single()['count'];

        // Today's revenue
        $this->db->query("SELECT COALESCE(SUM(final_amount), 0) as revenue
            FROM ride_bookings
            WHERE DATE(created_at) = CURDATE()
            AND payment_status = 'paid'
            AND {$companyFilter}");
        $this->bindCompanyId();
        $stats['today_revenue'] = $this->db->single()['revenue'];

        // This month's completed trips
        $this->db->query("SELECT COUNT(*) as count
            FROM ride_bookings
            WHERE MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
            AND status = 'completed'
            AND {$companyFilter}");
        $this->bindCompanyId();
        $stats['month_completed_trips'] = $this->db->single()['count'];

        return $stats;
    }

    /**
     * Get recent bookings
     */
    public function getRecentBookings($limit = 10) {
        $companyFilter = $this->getCompanyFilter('rb');
        $this->db->query("SELECT rb.*,
            p.first_name AS passenger_first_name,
            p.last_name AS passenger_last_name,
            u.first_name AS driver_first_name,
            u.last_name AS driver_last_name
            FROM ride_bookings rb
            LEFT JOIN passengers p ON rb.passenger_id = p.id
            LEFT JOIN transport_drivers td ON rb.driver_id = td.id
            LEFT JOIN users u ON td.user_id = u.id
            WHERE {$companyFilter}
            ORDER BY rb.created_at DESC
            LIMIT :limit");

        $this->bindCompanyId();
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
