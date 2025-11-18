<?php
/**
 * Vehicle Model
 */

class Vehicle extends Database {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all vehicles
     */
    public function getAllVehicles() {
        $this->query('SELECT * FROM vehicles ORDER BY created_at DESC');
        return $this->fetchAll();
    }

    /**
     * Get vehicle by ID
     */
    public function getVehicleById($id) {
        $this->query('SELECT * FROM vehicles WHERE id = :id');
        $this->bind(':id', $id);
        return $this->fetch();
    }

    /**
     * Get vehicles by status
     */
    public function getVehiclesByStatus($status) {
        $this->query('SELECT * FROM vehicles WHERE status = :status ORDER BY registration_number');
        $this->bind(':status', $status);
        return $this->fetchAll();
    }

    /**
     * Add new vehicle
     */
    public function addVehicle($data) {
        $this->query('INSERT INTO vehicles (
            registration_number, vin, brand, model, year, color, type, fuel_type,
            engine_capacity, power, transmission, seats, doors, weight, load_capacity,
            purchase_date, purchase_price, current_value, insurance_company,
            insurance_policy, insurance_expiry, registration_expiry, technical_control_expiry,
            odometer, fuel_tank_capacity, status, gps_device_id, notes, photo
        ) VALUES (
            :registration_number, :vin, :brand, :model, :year, :color, :type, :fuel_type,
            :engine_capacity, :power, :transmission, :seats, :doors, :weight, :load_capacity,
            :purchase_date, :purchase_price, :current_value, :insurance_company,
            :insurance_policy, :insurance_expiry, :registration_expiry, :technical_control_expiry,
            :odometer, :fuel_tank_capacity, :status, :gps_device_id, :notes, :photo
        )');

        $this->bind(':registration_number', $data['registration_number']);
        $this->bind(':vin', $data['vin'] ?? null);
        $this->bind(':brand', $data['brand']);
        $this->bind(':model', $data['model']);
        $this->bind(':year', $data['year'] ?? null);
        $this->bind(':color', $data['color'] ?? null);
        $this->bind(':type', $data['type']);
        $this->bind(':fuel_type', $data['fuel_type']);
        $this->bind(':engine_capacity', $data['engine_capacity'] ?? null);
        $this->bind(':power', $data['power'] ?? null);
        $this->bind(':transmission', $data['transmission'] ?? null);
        $this->bind(':seats', $data['seats'] ?? null);
        $this->bind(':doors', $data['doors'] ?? null);
        $this->bind(':weight', $data['weight'] ?? null);
        $this->bind(':load_capacity', $data['load_capacity'] ?? null);
        $this->bind(':purchase_date', $data['purchase_date'] ?? null);
        $this->bind(':purchase_price', $data['purchase_price'] ?? null);
        $this->bind(':current_value', $data['current_value'] ?? null);
        $this->bind(':insurance_company', $data['insurance_company'] ?? null);
        $this->bind(':insurance_policy', $data['insurance_policy'] ?? null);
        $this->bind(':insurance_expiry', $data['insurance_expiry'] ?? null);
        $this->bind(':registration_expiry', $data['registration_expiry'] ?? null);
        $this->bind(':technical_control_expiry', $data['technical_control_expiry'] ?? null);
        $this->bind(':odometer', $data['odometer'] ?? 0);
        $this->bind(':fuel_tank_capacity', $data['fuel_tank_capacity'] ?? null);
        $this->bind(':status', $data['status'] ?? 'active');
        $this->bind(':gps_device_id', $data['gps_device_id'] ?? null);
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':photo', $data['photo'] ?? null);

        if ($this->execute()) {
            return $this->lastInsertId();
        }

        return false;
    }

    /**
     * Update vehicle
     */
    public function updateVehicle($id, $data) {
        $this->query('UPDATE vehicles SET
            registration_number = :registration_number,
            vin = :vin,
            brand = :brand,
            model = :model,
            year = :year,
            color = :color,
            type = :type,
            fuel_type = :fuel_type,
            engine_capacity = :engine_capacity,
            power = :power,
            transmission = :transmission,
            seats = :seats,
            doors = :doors,
            weight = :weight,
            load_capacity = :load_capacity,
            purchase_date = :purchase_date,
            purchase_price = :purchase_price,
            current_value = :current_value,
            insurance_company = :insurance_company,
            insurance_policy = :insurance_policy,
            insurance_expiry = :insurance_expiry,
            registration_expiry = :registration_expiry,
            technical_control_expiry = :technical_control_expiry,
            odometer = :odometer,
            fuel_tank_capacity = :fuel_tank_capacity,
            status = :status,
            gps_device_id = :gps_device_id,
            notes = :notes,
            photo = :photo
            WHERE id = :id');

        $this->bind(':id', $id);
        $this->bind(':registration_number', $data['registration_number']);
        $this->bind(':vin', $data['vin'] ?? null);
        $this->bind(':brand', $data['brand']);
        $this->bind(':model', $data['model']);
        $this->bind(':year', $data['year'] ?? null);
        $this->bind(':color', $data['color'] ?? null);
        $this->bind(':type', $data['type']);
        $this->bind(':fuel_type', $data['fuel_type']);
        $this->bind(':engine_capacity', $data['engine_capacity'] ?? null);
        $this->bind(':power', $data['power'] ?? null);
        $this->bind(':transmission', $data['transmission'] ?? null);
        $this->bind(':seats', $data['seats'] ?? null);
        $this->bind(':doors', $data['doors'] ?? null);
        $this->bind(':weight', $data['weight'] ?? null);
        $this->bind(':load_capacity', $data['load_capacity'] ?? null);
        $this->bind(':purchase_date', $data['purchase_date'] ?? null);
        $this->bind(':purchase_price', $data['purchase_price'] ?? null);
        $this->bind(':current_value', $data['current_value'] ?? null);
        $this->bind(':insurance_company', $data['insurance_company'] ?? null);
        $this->bind(':insurance_policy', $data['insurance_policy'] ?? null);
        $this->bind(':insurance_expiry', $data['insurance_expiry'] ?? null);
        $this->bind(':registration_expiry', $data['registration_expiry'] ?? null);
        $this->bind(':technical_control_expiry', $data['technical_control_expiry'] ?? null);
        $this->bind(':odometer', $data['odometer'] ?? 0);
        $this->bind(':fuel_tank_capacity', $data['fuel_tank_capacity'] ?? null);
        $this->bind(':status', $data['status']);
        $this->bind(':gps_device_id', $data['gps_device_id'] ?? null);
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':photo', $data['photo'] ?? null);

        return $this->execute();
    }

    /**
     * Delete vehicle
     */
    public function deleteVehicle($id) {
        $this->query('DELETE FROM vehicles WHERE id = :id');
        $this->bind(':id', $id);
        return $this->execute();
    }

    /**
     * Count total vehicles
     */
    public function countVehicles() {
        $this->query('SELECT COUNT(*) as total FROM vehicles');
        $result = $this->fetch();
        return $result['total'];
    }

    /**
     * Count vehicles by status
     */
    public function countVehiclesByStatus($status) {
        $this->query('SELECT COUNT(*) as total FROM vehicles WHERE status = :status');
        $this->bind(':status', $status);
        $result = $this->fetch();
        return $result['total'];
    }

    /**
     * Get vehicles with expiring documents
     */
    public function getVehiclesWithExpiringDocuments($days = 30) {
        $this->query('SELECT * FROM vehicles
                      WHERE (insurance_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY))
                         OR (registration_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY))
                         OR (technical_control_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY))
                      ORDER BY insurance_expiry, registration_expiry, technical_control_expiry');
        $this->bind(':days', $days);
        return $this->fetchAll();
    }

    /**
     * Search vehicles
     */
    public function searchVehicles($keyword) {
        $this->query('SELECT * FROM vehicles
                      WHERE registration_number LIKE :keyword
                         OR vin LIKE :keyword
                         OR brand LIKE :keyword
                         OR model LIKE :keyword
                      ORDER BY registration_number');
        $this->bind(':keyword', '%' . $keyword . '%');
        return $this->fetchAll();
    }
}
