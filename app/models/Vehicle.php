<?php
/**
 * Vehicle Model - Multi-tenant enabled
 */

class Vehicle {
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
    private function getCompanyFilter($tableAlias = 'v') {
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

    /**
     * Get all vehicles
     */
    public function getAllVehicles() {
        $filter = $this->getCompanyFilter('v');
        $this->db->query("SELECT * FROM vehicles v WHERE {$filter} ORDER BY v.created_at DESC");
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Get vehicle by ID
     */
    public function getVehicleById($id) {
        $filter = $this->getCompanyFilter('v');
        $this->db->query("SELECT * FROM vehicles v WHERE v.id = :id AND {$filter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    /**
     * Get vehicles by status
     */
    public function getVehiclesByStatus($status) {
        $filter = $this->getCompanyFilter('v');
        $this->db->query("SELECT * FROM vehicles v WHERE v.status = :status AND {$filter} ORDER BY v.registration_number");
        $this->db->bind(':status', $status);
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Add new vehicle
     */
    public function addVehicle($data) {
        // Check vehicle limit
        if (!isSuperAdmin() && hasReachedVehicleLimit()) {
            return false;
        }

        $this->db->query('INSERT INTO vehicles (
            company_id, registration_number, vin, brand, model, year, color, type, fuel_type,
            engine_capacity, power, transmission, seats, doors, weight, load_capacity,
            purchase_date, purchase_price, current_value, insurance_company,
            insurance_policy, insurance_expiry, registration_expiry, technical_control_expiry,
            odometer, fuel_tank_capacity, status, gps_device_id, notes, photo
        ) VALUES (
            :company_id, :registration_number, :vin, :brand, :model, :year, :color, :type, :fuel_type,
            :engine_capacity, :power, :transmission, :seats, :doors, :weight, :load_capacity,
            :purchase_date, :purchase_price, :current_value, :insurance_company,
            :insurance_policy, :insurance_expiry, :registration_expiry, :technical_control_expiry,
            :odometer, :fuel_tank_capacity, :status, :gps_device_id, :notes, :photo
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':registration_number', $data['registration_number']);
        $this->db->bind(':vin', $data['vin'] ?? null);
        $this->db->bind(':brand', $data['brand']);
        $this->db->bind(':model', $data['model']);
        $this->db->bind(':year', $data['year'] ?? null);
        $this->db->bind(':color', $data['color'] ?? null);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':fuel_type', $data['fuel_type']);
        $this->db->bind(':engine_capacity', $data['engine_capacity'] ?? null);
        $this->db->bind(':power', $data['power'] ?? null);
        $this->db->bind(':transmission', $data['transmission'] ?? null);
        $this->db->bind(':seats', $data['seats'] ?? null);
        $this->db->bind(':doors', $data['doors'] ?? null);
        $this->db->bind(':weight', $data['weight'] ?? null);
        $this->db->bind(':load_capacity', $data['load_capacity'] ?? null);
        $this->db->bind(':purchase_date', $data['purchase_date'] ?? null);
        $this->db->bind(':purchase_price', $data['purchase_price'] ?? null);
        $this->db->bind(':current_value', $data['current_value'] ?? null);
        $this->db->bind(':insurance_company', $data['insurance_company'] ?? null);
        $this->db->bind(':insurance_policy', $data['insurance_policy'] ?? null);
        $this->db->bind(':insurance_expiry', $data['insurance_expiry'] ?? null);
        $this->db->bind(':registration_expiry', $data['registration_expiry'] ?? null);
        $this->db->bind(':technical_control_expiry', $data['technical_control_expiry'] ?? null);
        $this->db->bind(':odometer', $data['odometer'] ?? 0);
        $this->db->bind(':fuel_tank_capacity', $data['fuel_tank_capacity'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':gps_device_id', $data['gps_device_id'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':photo', $data['photo'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update vehicle
     */
    public function updateVehicle($id, $data) {
        $filter = $this->getCompanyFilter('v');
        
        $this->db->query('UPDATE vehicles v SET
            v.registration_number = :registration_number,
            v.vin = :vin,
            v.brand = :brand,
            v.model = :model,
            v.year = :year,
            v.color = :color,
            v.type = :type,
            v.fuel_type = :fuel_type,
            v.engine_capacity = :engine_capacity,
            v.power = :power,
            v.transmission = :transmission,
            v.seats = :seats,
            v.doors = :doors,
            v.weight = :weight,
            v.load_capacity = :load_capacity,
            v.purchase_date = :purchase_date,
            v.purchase_price = :purchase_price,
            v.current_value = :current_value,
            v.insurance_company = :insurance_company,
            v.insurance_policy = :insurance_policy,
            v.insurance_expiry = :insurance_expiry,
            v.registration_expiry = :registration_expiry,
            v.technical_control_expiry = :technical_control_expiry,
            v.odometer = :odometer,
            v.fuel_tank_capacity = :fuel_tank_capacity,
            v.status = :status,
            v.gps_device_id = :gps_device_id,
            v.notes = :notes,
            v.photo = :photo
            WHERE v.id = :id AND ' . $filter);

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':registration_number', $data['registration_number']);
        $this->db->bind(':vin', $data['vin'] ?? null);
        $this->db->bind(':brand', $data['brand']);
        $this->db->bind(':model', $data['model']);
        $this->db->bind(':year', $data['year'] ?? null);
        $this->db->bind(':color', $data['color'] ?? null);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':fuel_type', $data['fuel_type']);
        $this->db->bind(':engine_capacity', $data['engine_capacity'] ?? null);
        $this->db->bind(':power', $data['power'] ?? null);
        $this->db->bind(':transmission', $data['transmission'] ?? null);
        $this->db->bind(':seats', $data['seats'] ?? null);
        $this->db->bind(':doors', $data['doors'] ?? null);
        $this->db->bind(':weight', $data['weight'] ?? null);
        $this->db->bind(':load_capacity', $data['load_capacity'] ?? null);
        $this->db->bind(':purchase_date', $data['purchase_date'] ?? null);
        $this->db->bind(':purchase_price', $data['purchase_price'] ?? null);
        $this->db->bind(':current_value', $data['current_value'] ?? null);
        $this->db->bind(':insurance_company', $data['insurance_company'] ?? null);
        $this->db->bind(':insurance_policy', $data['insurance_policy'] ?? null);
        $this->db->bind(':insurance_expiry', $data['insurance_expiry'] ?? null);
        $this->db->bind(':registration_expiry', $data['registration_expiry'] ?? null);
        $this->db->bind(':technical_control_expiry', $data['technical_control_expiry'] ?? null);
        $this->db->bind(':odometer', $data['odometer'] ?? 0);
        $this->db->bind(':fuel_tank_capacity', $data['fuel_tank_capacity'] ?? null);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':gps_device_id', $data['gps_device_id'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':photo', $data['photo'] ?? null);

        return $this->db->execute();
    }

    /**
     * Delete vehicle
     */
    public function deleteVehicle($id) {
        $filter = $this->getCompanyFilter('v');
        $this->db->query('DELETE FROM vehicles WHERE id = :id AND ' . $filter);
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->execute();
    }

    /**
     * Count total vehicles
     */
    public function countVehicles() {
        $filter = $this->getCompanyFilter('v');
        $this->db->query("SELECT COUNT(*) as total FROM vehicles v WHERE {$filter}");
        $this->bindCompanyId();
        $result = $this->db->fetch();
        return $result['total'];
    }

    /**
     * Count vehicles by status
     */
    public function countVehiclesByStatus($status) {
        $filter = $this->getCompanyFilter('v');
        $this->db->query("SELECT COUNT(*) as total FROM vehicles v WHERE v.status = :status AND {$filter}");
        $this->db->bind(':status', $status);
        $this->bindCompanyId();
        $result = $this->db->fetch();
        return $result['total'];
    }

    /**
     * Get vehicles with expiring documents
     */
    public function getVehiclesWithExpiringDocuments($days = 30) {
        $filter = $this->getCompanyFilter('v');
        $this->db->query("SELECT * FROM vehicles v
                      WHERE {$filter}
                      AND ((v.insurance_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY))
                         OR (v.registration_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY))
                         OR (v.technical_control_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)))
                      ORDER BY v.insurance_expiry, v.registration_expiry, v.technical_control_expiry");
        $this->bindCompanyId();
        $this->db->bind(':days', $days);
        return $this->db->fetchAll();
    }

    /**
     * Search vehicles
     */
    public function searchVehicles($keyword) {
        $filter = $this->getCompanyFilter('v');
        $this->db->query("SELECT * FROM vehicles v
                      WHERE {$filter}
                      AND (v.registration_number LIKE :keyword
                         OR v.vin LIKE :keyword
                         OR v.brand LIKE :keyword
                         OR v.model LIKE :keyword)
                      ORDER BY v.registration_number");
        $this->bindCompanyId();
        $this->db->bind(':keyword', '%' . $keyword . '%');
        return $this->db->fetchAll();
    }

    /**
     * Get dashboard stats
     */
    public function getDashboardStats() {
        $filter = $this->getCompanyFilter('v');
        $this->db->query("SELECT
            COUNT(*) as total_vehicles,
            SUM(CASE WHEN v.status = 'active' THEN 1 ELSE 0 END) as active_vehicles,
            SUM(CASE WHEN v.status = 'maintenance' THEN 1 ELSE 0 END) as in_maintenance,
            SUM(CASE WHEN v.status = 'repair' THEN 1 ELSE 0 END) as in_repair,
            SUM(CASE WHEN v.status = 'inactive' THEN 1 ELSE 0 END) as inactive_vehicles
            FROM vehicles v WHERE {$filter}");
        $this->bindCompanyId();
        return $this->db->fetch();
    }
}
