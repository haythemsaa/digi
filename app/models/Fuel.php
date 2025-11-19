<?php

class Fuel {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();

        // Ensure company context exists
        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    /**
     * Get company filter for SQL queries
     */
    private function getCompanyFilter($tableAlias = 'fc') {
        if (isSuperAdmin()) {
            return '1=1'; // No filter for super admins
        }
        return "{$tableAlias}.company_id = :company_id";
    }

    /**
     * Bind company ID to query
     */
    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    // ========================================
    // FUEL CARDS
    // ========================================

    public function getAllFuelCards() {
        $companyFilter = $this->getCompanyFilter('fc');

        $this->db->query("SELECT fc.*,
            v.registration_number,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name
            FROM fuel_cards fc
            LEFT JOIN vehicles v ON fc.vehicle_id = v.id
            LEFT JOIN drivers d ON fc.driver_id = d.id
            WHERE {$companyFilter}
            ORDER BY fc.created_at DESC");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getFuelCardById($id) {
        $companyFilter = $this->getCompanyFilter('fc');

        $this->db->query("SELECT * FROM fuel_cards fc WHERE fc.id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function createFuelCard($data) {
        $this->db->query("INSERT INTO fuel_cards
            (company_id, card_number, card_type, provider, vehicle_id, driver_id, daily_limit, monthly_limit, issue_date, expiry_date, is_active)
            VALUES (:company_id, :card_number, :card_type, :provider, :vehicle_id, :driver_id, :daily_limit, :monthly_limit, :issue_date, :expiry_date, :is_active)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':card_number', $data['card_number']);
        $this->db->bind(':card_type', $data['card_type'] ?? 'physical');
        $this->db->bind(':provider', $data['provider'] ?? null);
        $this->db->bind(':vehicle_id', $data['vehicle_id'] ?? null);
        $this->db->bind(':driver_id', $data['driver_id'] ?? null);
        $this->db->bind(':daily_limit', $data['daily_limit'] ?? null);
        $this->db->bind(':monthly_limit', $data['monthly_limit'] ?? null);
        $this->db->bind(':issue_date', $data['issue_date'] ?? null);
        $this->db->bind(':expiry_date', $data['expiry_date'] ?? null);
        $this->db->bind(':is_active', $data['is_active'] ?? 1);

        return $this->db->execute();
    }

    // ========================================
    // FUEL TRANSACTIONS
    // ========================================

    public function getAllTransactions($limit = 100) {
        $companyFilter = $this->getCompanyFilter('ft');

        $this->db->query("SELECT ft.*,
            v.registration_number,
            v.brand, v.model,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name,
            fc.card_number
            FROM fuel_transactions ft
            LEFT JOIN vehicles v ON ft.vehicle_id = v.id
            LEFT JOIN drivers d ON ft.driver_id = d.id
            LEFT JOIN fuel_cards fc ON ft.fuel_card_id = fc.id
            WHERE {$companyFilter}
            ORDER BY ft.transaction_date DESC
            LIMIT :limit");

        $this->bindCompanyId();
        $this->db->bind(':limit', $limit);
        return $this->db->fetchAll();
    }

    public function getTransactionById($id) {
        $companyFilter = $this->getCompanyFilter('ft');

        $this->db->query("SELECT ft.*,
            v.registration_number, v.brand, v.model,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name,
            fc.card_number
            FROM fuel_transactions ft
            LEFT JOIN vehicles v ON ft.vehicle_id = v.id
            LEFT JOIN drivers d ON ft.driver_id = d.id
            LEFT JOIN fuel_cards fc ON ft.fuel_card_id = fc.id
            WHERE ft.id = :id AND {$companyFilter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function createTransaction($data) {
        // Generate transaction number
        $transactionNumber = 'FUEL' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->db->query("INSERT INTO fuel_transactions
            (company_id, transaction_number, transaction_date, vehicle_id, driver_id, fuel_card_id, fuel_type,
             quantity_liters, unit_price, total_amount, odometer_reading, station_name,
             payment_method, is_full_tank, notes)
            VALUES (:company_id, :transaction_number, :transaction_date, :vehicle_id, :driver_id, :fuel_card_id, :fuel_type,
                    :quantity_liters, :unit_price, :total_amount, :odometer_reading, :station_name,
                    :payment_method, :is_full_tank, :notes)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':transaction_number', $transactionNumber);
        $this->db->bind(':transaction_date', $data['transaction_date']);
        $this->db->bind(':vehicle_id', $data['vehicle_id']);
        $this->db->bind(':driver_id', $data['driver_id'] ?? null);
        $this->db->bind(':fuel_card_id', $data['fuel_card_id'] ?? null);
        $this->db->bind(':fuel_type', $data['fuel_type']);
        $this->db->bind(':quantity_liters', $data['quantity_liters']);
        $this->db->bind(':unit_price', $data['unit_price']);
        $this->db->bind(':total_amount', $data['total_amount']);
        $this->db->bind(':odometer_reading', $data['odometer_reading'] ?? null);
        $this->db->bind(':station_name', $data['station_name'] ?? null);
        $this->db->bind(':payment_method', $data['payment_method'] ?? 'fuel_card');
        $this->db->bind(':is_full_tank', $data['is_full_tank'] ?? 1);
        $this->db->bind(':notes', $data['notes'] ?? null);

        if ($this->db->execute()) {
            // Calculate consumption if odometer reading is provided
            if (!empty($data['odometer_reading'])) {
                $this->calculateConsumption($data['vehicle_id']);
            }
            return true;
        }

        return false;
    }

    // ========================================
    // CONSUMPTION ANALYTICS
    // ========================================

    public function calculateConsumption($vehicleId) {
        $companyFilter = $this->getCompanyFilter('ft');

        // Get last two fill-ups
        $this->db->query("SELECT * FROM fuel_transactions ft
            WHERE ft.vehicle_id = :vehicle_id
            AND {$companyFilter}
            AND ft.odometer_reading IS NOT NULL
            AND ft.is_full_tank = 1
            ORDER BY ft.transaction_date DESC LIMIT 2");

        $this->db->bind(':vehicle_id', $vehicleId);
        $this->bindCompanyId();
        $fillups = $this->db->fetchAll();

        if (count($fillups) >= 2) {
            $distanceKm = $fillups[0]['odometer_reading'] - $fillups[1]['odometer_reading'];
            $liters = $fillups[0]['quantity_liters'];

            if ($distanceKm > 0) {
                $consumption = ($liters / $distanceKm) * 100; // L/100km
                return $consumption;
            }
        }

        return null;
    }

    public function getVehicleConsumption($vehicleId, $startDate = null, $endDate = null) {
        if (!$startDate) $startDate = date('Y-m-d', strtotime('-30 days'));
        if (!$endDate) $endDate = date('Y-m-d');

        $companyFilter = $this->getCompanyFilter('ft');

        $this->db->query("SELECT
            SUM(quantity_liters) as total_fuel,
            SUM(total_amount) as total_cost,
            COUNT(*) as total_fillups,
            AVG(unit_price) as avg_price_per_liter,
            MAX(odometer_reading) - MIN(odometer_reading) as distance_km
            FROM fuel_transactions ft
            WHERE ft.vehicle_id = :vehicle_id
            AND {$companyFilter}
            AND DATE(ft.transaction_date) BETWEEN :start_date AND :end_date
            AND ft.odometer_reading IS NOT NULL");

        $this->db->bind(':vehicle_id', $vehicleId);
        $this->bindCompanyId();
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);

        $result = $this->db->fetch();

        // Calculate consumption
        if ($result && $result['distance_km'] > 0) {
            $result['consumption_per_100km'] = ($result['total_fuel'] / $result['distance_km']) * 100;
            $result['cost_per_km'] = $result['total_cost'] / $result['distance_km'];
        }

        return $result;
    }

    public function getFleetConsumptionStats() {
        $companyFilter = $this->getCompanyFilter('v');

        $this->db->query("SELECT
            v.id, v.registration_number, v.brand, v.model,
            COUNT(ft.id) as total_fillups,
            SUM(ft.quantity_liters) as total_fuel,
            SUM(ft.total_amount) as total_cost,
            AVG(ft.unit_price) as avg_price
            FROM vehicles v
            LEFT JOIN fuel_transactions ft ON v.id = ft.vehicle_id
            WHERE {$companyFilter}
            AND ft.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY v.id
            ORDER BY total_cost DESC");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getDashboardStats() {
        $companyFilter = $this->getCompanyFilter('ft');

        // Total fuel cost (last 30 days)
        $this->db->query("SELECT
            SUM(total_amount) as total_cost,
            SUM(quantity_liters) as total_liters,
            COUNT(*) as total_transactions
            FROM fuel_transactions ft
            WHERE {$companyFilter}
            AND ft.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $this->bindCompanyId();
        $stats = $this->db->fetch();

        // Average consumption
        $this->db->query("SELECT AVG(unit_price) as avg_price FROM fuel_transactions ft
            WHERE {$companyFilter}
            AND ft.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $this->bindCompanyId();
        $priceData = $this->db->fetch();
        $stats['avg_price_per_liter'] = $priceData['avg_price'] ?? 0;

        return $stats;
    }

    public function getMonthlyTrends($months = 6) {
        $companyFilter = $this->getCompanyFilter('ft');

        $this->db->query("SELECT
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(total_amount) as total_cost,
            SUM(quantity_liters) as total_liters,
            COUNT(*) as transactions,
            AVG(unit_price) as avg_price
            FROM fuel_transactions ft
            WHERE {$companyFilter}
            AND ft.transaction_date >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY month
            ORDER BY month ASC");

        $this->bindCompanyId();
        $this->db->bind(':months', $months);
        return $this->db->fetchAll();
    }

    // ========================================
    // FUEL PRICES
    // ========================================

    public function getCurrentPrices() {
        // Fuel prices are global, not company-specific
        $this->db->query("SELECT * FROM fuel_prices
            WHERE effective_date = (
                SELECT MAX(effective_date) FROM fuel_prices fp2
                WHERE fp2.fuel_type = fuel_prices.fuel_type
            )
            ORDER BY fuel_type");

        return $this->db->fetchAll();
    }

    public function addFuelPrice($data) {
        $this->db->query("INSERT INTO fuel_prices
            (fuel_type, price_per_liter, effective_date, provider, is_official_price)
            VALUES (:fuel_type, :price_per_liter, :effective_date, :provider, :is_official_price)");

        $this->db->bind(':fuel_type', $data['fuel_type']);
        $this->db->bind(':price_per_liter', $data['price_per_liter']);
        $this->db->bind(':effective_date', $data['effective_date']);
        $this->db->bind(':provider', $data['provider'] ?? null);
        $this->db->bind(':is_official_price', $data['is_official_price'] ?? 0);

        return $this->db->execute();
    }

    // ========================================
    // ALERTS
    // ========================================

    public function getActiveAlerts() {
        $companyFilter = $this->getCompanyFilter('fa');

        $this->db->query("SELECT fa.*,
            v.registration_number,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name
            FROM fuel_alerts fa
            LEFT JOIN vehicles v ON fa.vehicle_id = v.id
            LEFT JOIN drivers d ON fa.driver_id = d.id
            WHERE fa.is_resolved = 0
            AND {$companyFilter}
            ORDER BY fa.severity DESC, fa.created_at DESC");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function createAlert($data) {
        $this->db->query("INSERT INTO fuel_alerts
            (company_id, alert_type, vehicle_id, driver_id, severity, title, description, threshold_value, actual_value)
            VALUES (:company_id, :alert_type, :vehicle_id, :driver_id, :severity, :title, :description, :threshold_value, :actual_value)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':alert_type', $data['alert_type']);
        $this->db->bind(':vehicle_id', $data['vehicle_id'] ?? null);
        $this->db->bind(':driver_id', $data['driver_id'] ?? null);
        $this->db->bind(':severity', $data['severity'] ?? 'medium');
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':threshold_value', $data['threshold_value'] ?? null);
        $this->db->bind(':actual_value', $data['actual_value'] ?? null);

        return $this->db->execute();
    }

    public function resolveAlert($alertId, $userId = null) {
        $companyFilter = $this->getCompanyFilter('fa');

        $this->db->query("UPDATE fuel_alerts fa SET
            fa.is_resolved = 1,
            fa.resolved_at = NOW(),
            fa.resolved_by = :resolved_by
            WHERE fa.id = :id AND {$companyFilter}");

        $this->db->bind(':id', $alertId);
        $this->db->bind(':resolved_by', $userId);
        $this->bindCompanyId();

        return $this->db->execute();
    }
}
