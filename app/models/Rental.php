<?php
/**
 * Rental Model
 * Manages vehicle rental/location operations
 */

class Rental extends Model {

    /**
     * Get all rental contracts
     */
    public function getAllContracts($filters = []) {
        $sql = "SELECT rc.*,
                       v.registration_number, v.make, v.model,
                       c.name as client_name, c.phone as client_phone,
                       CONCAT(u1.first_name, ' ', u1.last_name) as created_by_name
                FROM rental_contracts rc
                LEFT JOIN vehicles v ON rc.vehicle_id = v.id
                LEFT JOIN clients c ON rc.client_id = c.id
                LEFT JOIN users u1 ON rc.created_by = u1.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND rc.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= " AND rc.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['vehicle_id'])) {
            $sql .= " AND rc.vehicle_id = ?";
            $params[] = $filters['vehicle_id'];
        }

        if (!empty($filters['client_id'])) {
            $sql .= " AND rc.client_id = ?";
            $params[] = $filters['client_id'];
        }

        $sql .= " ORDER BY rc.created_at DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get contract by ID
     */
    public function getContractById($id) {
        $this->db->query("SELECT rc.*,
                                 v.registration_number, v.make, v.model, v.year, v.color,
                                 c.name as client_name, c.phone as client_phone,
                                 c.email as client_email, c.address as client_address,
                                 c.license_number, c.license_expiry,
                                 rr.name as rate_name
                          FROM rental_contracts rc
                          LEFT JOIN vehicles v ON rc.vehicle_id = v.id
                          LEFT JOIN clients c ON rc.client_id = c.id
                          LEFT JOIN rental_rates rr ON rc.rate_id = rr.id
                          WHERE rc.id = ?");
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    /**
     * Create new rental contract
     */
    public function createContract($data) {
        // Generate contract number
        $contractNumber = $this->generateContractNumber();

        // Calculate totals
        $startDate = new DateTime($data['start_date']);
        $endDate = new DateTime($data['end_date']);
        $totalDays = $endDate->diff($startDate)->days + 1;

        $dailyRate = floatval($data['daily_rate']);
        $subtotal = $dailyRate * $totalDays;

        $insuranceRate = floatval($data['insurance_rate'] ?? 0);
        $insuranceTotal = $insuranceRate * $totalDays;

        $taxRate = floatval($data['tax_rate'] ?? 0);
        $taxAmount = ($subtotal + $insuranceTotal) * ($taxRate / 100);

        $totalAmount = $subtotal + $insuranceTotal + $taxAmount;

        $this->db->query("INSERT INTO rental_contracts
                         (contract_number, client_id, vehicle_id, rate_id,
                          start_date, end_date, daily_rate,
                          insurance_rate, insurance_total,
                          tax_rate, tax_amount,
                          total_days, deposit, total_amount,
                          pickup_location, return_location,
                          notes, status, payment_status, created_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'reserved', 'pending', ?)");

        $this->db->bind(1, $contractNumber);
        $this->db->bind(2, $data['client_id']);
        $this->db->bind(3, $data['vehicle_id']);
        $this->db->bind(4, $data['rate_id'] ?? null);
        $this->db->bind(5, $data['start_date']);
        $this->db->bind(6, $data['end_date']);
        $this->db->bind(7, $dailyRate);
        $this->db->bind(8, $insuranceRate);
        $this->db->bind(9, $insuranceTotal);
        $this->db->bind(10, $taxRate);
        $this->db->bind(11, $taxAmount);
        $this->db->bind(12, $totalDays);
        $this->db->bind(13, $data['deposit'] ?? 0);
        $this->db->bind(14, $totalAmount);
        $this->db->bind(15, $data['pickup_location'] ?? null);
        $this->db->bind(16, $data['return_location'] ?? null);
        $this->db->bind(17, $data['notes'] ?? null);
        $this->db->bind(18, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update rental contract
     */
    public function updateContract($id, $data) {
        // Recalculate if dates or rates changed
        if (isset($data['start_date']) && isset($data['end_date']) && isset($data['daily_rate'])) {
            $startDate = new DateTime($data['start_date']);
            $endDate = new DateTime($data['end_date']);
            $totalDays = $endDate->diff($startDate)->days + 1;

            $dailyRate = floatval($data['daily_rate']);
            $subtotal = $dailyRate * $totalDays;

            $insuranceRate = floatval($data['insurance_rate'] ?? 0);
            $insuranceTotal = $insuranceRate * $totalDays;

            $taxRate = floatval($data['tax_rate'] ?? 0);
            $taxAmount = ($subtotal + $insuranceTotal) * ($taxRate / 100);

            $totalAmount = $subtotal + $insuranceTotal + $taxAmount;

            $this->db->query("UPDATE rental_contracts SET
                             client_id = ?, vehicle_id = ?, rate_id = ?,
                             start_date = ?, end_date = ?, daily_rate = ?,
                             insurance_rate = ?, insurance_total = ?,
                             tax_rate = ?, tax_amount = ?,
                             total_days = ?, deposit = ?, total_amount = ?,
                             pickup_location = ?, return_location = ?, notes = ?
                             WHERE id = ?");

            $this->db->bind(1, $data['client_id']);
            $this->db->bind(2, $data['vehicle_id']);
            $this->db->bind(3, $data['rate_id'] ?? null);
            $this->db->bind(4, $data['start_date']);
            $this->db->bind(5, $data['end_date']);
            $this->db->bind(6, $dailyRate);
            $this->db->bind(7, $insuranceRate);
            $this->db->bind(8, $insuranceTotal);
            $this->db->bind(9, $taxRate);
            $this->db->bind(10, $taxAmount);
            $this->db->bind(11, $totalDays);
            $this->db->bind(12, $data['deposit'] ?? 0);
            $this->db->bind(13, $totalAmount);
            $this->db->bind(14, $data['pickup_location'] ?? null);
            $this->db->bind(15, $data['return_location'] ?? null);
            $this->db->bind(16, $data['notes'] ?? null);
            $this->db->bind(17, $id);

            return $this->db->execute();
        }

        return false;
    }

    /**
     * Update contract status
     */
    public function updateContractStatus($id, $status) {
        $this->db->query("UPDATE rental_contracts SET status = ? WHERE id = ?");
        $this->db->bind(1, $status);
        $this->db->bind(2, $id);
        return $this->db->execute();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($id, $paymentStatus) {
        $this->db->query("UPDATE rental_contracts SET payment_status = ? WHERE id = ?");
        $this->db->bind(1, $paymentStatus);
        $this->db->bind(2, $id);
        return $this->db->execute();
    }

    /**
     * Delete contract
     */
    public function deleteContract($id) {
        $this->db->query("DELETE FROM rental_contracts WHERE id = ?");
        $this->db->bind(1, $id);
        return $this->db->execute();
    }

    /**
     * Generate unique contract number
     */
    private function generateContractNumber() {
        $year = date('Y');
        $prefix = 'RENT-' . $year . '-';

        $this->db->query("SELECT contract_number FROM rental_contracts
                         WHERE contract_number LIKE ?
                         ORDER BY contract_number DESC LIMIT 1");
        $this->db->bind(1, $prefix . '%');
        $result = $this->db->single();

        if ($result) {
            $lastNumber = intval(substr($result['contract_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Check vehicle availability
     */
    public function checkVehicleAvailability($vehicleId, $startDate, $endDate, $excludeContractId = null) {
        $sql = "SELECT COUNT(*) as count FROM rental_contracts
                WHERE vehicle_id = ?
                AND status IN ('reserved', 'active')
                AND (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date >= ? AND end_date <= ?)
                )";

        if ($excludeContractId) {
            $sql .= " AND id != ?";
        }

        $this->db->query($sql);
        $this->db->bind(1, $vehicleId);
        $this->db->bind(2, $startDate);
        $this->db->bind(3, $startDate);
        $this->db->bind(4, $endDate);
        $this->db->bind(5, $endDate);
        $this->db->bind(6, $startDate);
        $this->db->bind(7, $endDate);

        if ($excludeContractId) {
            $this->db->bind(8, $excludeContractId);
        }

        $result = $this->db->single();
        return $result['count'] == 0;
    }

    // ==================== RENTAL RATES ====================

    /**
     * Get all rental rates
     */
    public function getAllRates() {
        $this->db->query("SELECT rr.*, v.registration_number, v.make, v.model
                         FROM rental_rates rr
                         LEFT JOIN vehicles v ON rr.vehicle_id = v.id
                         WHERE rr.is_active = 1
                         ORDER BY rr.created_at DESC");
        return $this->db->resultSet();
    }

    /**
     * Get rate by ID
     */
    public function getRateById($id) {
        $this->db->query("SELECT * FROM rental_rates WHERE id = ?");
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    /**
     * Get rates by vehicle
     */
    public function getRatesByVehicle($vehicleId) {
        $this->db->query("SELECT * FROM rental_rates WHERE vehicle_id = ? AND is_active = 1");
        $this->db->bind(1, $vehicleId);
        return $this->db->resultSet();
    }

    /**
     * Create rental rate
     */
    public function createRate($data) {
        $this->db->query("INSERT INTO rental_rates
                         (name, vehicle_id, daily_rate, weekly_rate, monthly_rate,
                          insurance_rate, deposit, mileage_limit, excess_km_rate, is_active)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $data['name']);
        $this->db->bind(2, $data['vehicle_id'] ?? null);
        $this->db->bind(3, $data['daily_rate']);
        $this->db->bind(4, $data['weekly_rate'] ?? null);
        $this->db->bind(5, $data['monthly_rate'] ?? null);
        $this->db->bind(6, $data['insurance_rate'] ?? 0);
        $this->db->bind(7, $data['deposit'] ?? 0);
        $this->db->bind(8, $data['mileage_limit'] ?? null);
        $this->db->bind(9, $data['excess_km_rate'] ?? 0);
        $this->db->bind(10, $data['is_active'] ?? 1);

        return $this->db->execute();
    }

    /**
     * Update rental rate
     */
    public function updateRate($id, $data) {
        $this->db->query("UPDATE rental_rates SET
                         name = ?, vehicle_id = ?, daily_rate = ?,
                         weekly_rate = ?, monthly_rate = ?,
                         insurance_rate = ?, deposit = ?,
                         mileage_limit = ?, excess_km_rate = ?, is_active = ?
                         WHERE id = ?");

        $this->db->bind(1, $data['name']);
        $this->db->bind(2, $data['vehicle_id'] ?? null);
        $this->db->bind(3, $data['daily_rate']);
        $this->db->bind(4, $data['weekly_rate'] ?? null);
        $this->db->bind(5, $data['monthly_rate'] ?? null);
        $this->db->bind(6, $data['insurance_rate'] ?? 0);
        $this->db->bind(7, $data['deposit'] ?? 0);
        $this->db->bind(8, $data['mileage_limit'] ?? null);
        $this->db->bind(9, $data['excess_km_rate'] ?? 0);
        $this->db->bind(10, $data['is_active'] ?? 1);
        $this->db->bind(11, $id);

        return $this->db->execute();
    }

    // ==================== INSPECTIONS ====================

    /**
     * Get contract inspections
     */
    public function getContractInspections($contractId) {
        $this->db->query("SELECT ri.*, CONCAT(u.first_name, ' ', u.last_name) as inspector_name
                         FROM rental_inspections ri
                         LEFT JOIN users u ON ri.inspector_id = u.id
                         WHERE ri.contract_id = ?
                         ORDER BY ri.inspection_date DESC");
        $this->db->bind(1, $contractId);
        return $this->db->resultSet();
    }

    /**
     * Create inspection
     */
    public function createInspection($data) {
        $this->db->query("INSERT INTO rental_inspections
                         (contract_id, inspection_type, inspection_date,
                          mileage, fuel_level, exterior_condition, interior_condition,
                          damages, notes, photos, inspector_id)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $data['contract_id']);
        $this->db->bind(2, $data['inspection_type']);
        $this->db->bind(3, $data['inspection_date']);
        $this->db->bind(4, $data['mileage']);
        $this->db->bind(5, $data['fuel_level']);
        $this->db->bind(6, $data['exterior_condition'] ?? null);
        $this->db->bind(7, $data['interior_condition'] ?? null);
        $this->db->bind(8, $data['damages'] ?? null);
        $this->db->bind(9, $data['notes'] ?? null);
        $this->db->bind(10, $data['photos'] ?? null);
        $this->db->bind(11, $_SESSION['user_id'] ?? null);

        return $this->db->execute();
    }

    // ==================== PAYMENTS ====================

    /**
     * Get contract payments
     */
    public function getContractPayments($contractId) {
        $this->db->query("SELECT rp.*, CONCAT(u.first_name, ' ', u.last_name) as received_by_name
                         FROM rental_payments rp
                         LEFT JOIN users u ON rp.received_by = u.id
                         WHERE rp.contract_id = ?
                         ORDER BY rp.payment_date DESC");
        $this->db->bind(1, $contractId);
        return $this->db->resultSet();
    }

    /**
     * Add payment
     */
    public function addPayment($data) {
        $this->db->query("INSERT INTO rental_payments
                         (contract_id, payment_date, amount, payment_method,
                          payment_type, reference, notes, received_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $data['contract_id']);
        $this->db->bind(2, $data['payment_date']);
        $this->db->bind(3, $data['amount']);
        $this->db->bind(4, $data['payment_method']);
        $this->db->bind(5, $data['payment_type']);
        $this->db->bind(6, $data['reference'] ?? null);
        $this->db->bind(7, $data['notes'] ?? null);
        $this->db->bind(8, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            // Update payment status
            $this->updatePaymentStatusByBalance($data['contract_id']);
            return true;
        }
        return false;
    }

    /**
     * Update payment status based on balance
     */
    private function updatePaymentStatusByBalance($contractId) {
        // Get contract total
        $contract = $this->getContractById($contractId);
        $totalAmount = floatval($contract['total_amount']);

        // Get total payments
        $this->db->query("SELECT SUM(amount) as total_paid FROM rental_payments WHERE contract_id = ?");
        $this->db->bind(1, $contractId);
        $result = $this->db->single();
        $totalPaid = floatval($result['total_paid'] ?? 0);

        // Determine status
        if ($totalPaid >= $totalAmount) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        } else {
            $status = 'pending';
        }

        $this->updatePaymentStatus($contractId, $status);
    }

    /**
     * Get rental statistics
     */
    public function getRentalStats() {
        $stats = [];

        // Active contracts
        $this->db->query("SELECT COUNT(*) as count FROM rental_contracts WHERE status = 'active'");
        $result = $this->db->single();
        $stats['active_contracts'] = $result['count'];

        // Reserved contracts
        $this->db->query("SELECT COUNT(*) as count FROM rental_contracts WHERE status = 'reserved'");
        $result = $this->db->single();
        $stats['reserved_contracts'] = $result['count'];

        // This month revenue
        $this->db->query("SELECT SUM(rp.amount) as revenue
                         FROM rental_payments rp
                         WHERE MONTH(rp.payment_date) = MONTH(CURRENT_DATE())
                         AND YEAR(rp.payment_date) = YEAR(CURRENT_DATE())");
        $result = $this->db->single();
        $stats['monthly_revenue'] = $result['revenue'] ?? 0;

        // Available vehicles for rental
        $this->db->query("SELECT COUNT(*) as count FROM vehicles
                         WHERE status = 'available'
                         AND id NOT IN (
                             SELECT vehicle_id FROM rental_contracts
                             WHERE status IN ('reserved', 'active')
                         )");
        $result = $this->db->single();
        $stats['available_vehicles'] = $result['count'];

        return $stats;
    }
}
