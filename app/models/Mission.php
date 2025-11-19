<?php

class Mission {
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
    private function getCompanyFilter($tableAlias = 'm') {
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
    // MISSIONS CRUD
    // ========================================

    public function getAllMissions($status = null, $limit = 100) {
        $companyFilter = $this->getCompanyFilter('m');
        $statusFilter = $status ? "AND m.status = :status" : "";

        $sql = "SELECT m.*,
            v.registration_number,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name,
            mb.total_amount, mb.payment_status
            FROM missions m
            LEFT JOIN vehicles v ON m.assigned_vehicle_id = v.id
            LEFT JOIN drivers d ON m.assigned_driver_id = d.id
            LEFT JOIN mission_billing mb ON m.id = mb.mission_id
            WHERE {$companyFilter} {$statusFilter}
            ORDER BY m.created_at DESC LIMIT :limit";

        $this->db->query($sql);
        $this->bindCompanyId();

        if ($status) {
            $this->db->bind(':status', $status);
        }

        $this->db->bind(':limit', $limit);
        return $this->db->fetchAll();
    }

    public function getMissionById($id) {
        $companyFilter = $this->getCompanyFilter('m');

        $this->db->query("SELECT m.*,
            v.registration_number, v.brand, v.model,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name,
            d.phone as driver_phone
            FROM missions m
            LEFT JOIN vehicles v ON m.assigned_vehicle_id = v.id
            LEFT JOIN drivers d ON m.assigned_driver_id = d.id
            WHERE m.id = :id AND {$companyFilter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function createMission($data) {
        // Generate mission number
        $missionNumber = 'MSN' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->db->query("INSERT INTO missions
            (company_id, mission_number, mission_type, client_name, client_phone, client_email, client_address,
             description, priority, status, scheduled_start, scheduled_end,
             assigned_vehicle_id, assigned_driver_id,
             pickup_location, pickup_latitude, pickup_longitude,
             delivery_location, delivery_latitude, delivery_longitude,
             estimated_distance_km, estimated_duration_minutes, notes, created_by)
            VALUES (:company_id, :mission_number, :mission_type, :client_name, :client_phone, :client_email, :client_address,
                    :description, :priority, :status, :scheduled_start, :scheduled_end,
                    :assigned_vehicle_id, :assigned_driver_id,
                    :pickup_location, :pickup_latitude, :pickup_longitude,
                    :delivery_location, :delivery_latitude, :delivery_longitude,
                    :estimated_distance_km, :estimated_duration_minutes, :notes, :created_by)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':mission_number', $missionNumber);
        $this->db->bind(':mission_type', $data['mission_type']);
        $this->db->bind(':client_name', $data['client_name']);
        $this->db->bind(':client_phone', $data['client_phone'] ?? null);
        $this->db->bind(':client_email', $data['client_email'] ?? null);
        $this->db->bind(':client_address', $data['client_address'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':priority', $data['priority'] ?? 'normal');
        $this->db->bind(':status', 'pending');
        $this->db->bind(':scheduled_start', $data['scheduled_start'] ?? null);
        $this->db->bind(':scheduled_end', $data['scheduled_end'] ?? null);
        $this->db->bind(':assigned_vehicle_id', $data['assigned_vehicle_id'] ?? null);
        $this->db->bind(':assigned_driver_id', $data['assigned_driver_id'] ?? null);
        $this->db->bind(':pickup_location', $data['pickup_location'] ?? null);
        $this->db->bind(':pickup_latitude', $data['pickup_latitude'] ?? null);
        $this->db->bind(':pickup_longitude', $data['pickup_longitude'] ?? null);
        $this->db->bind(':delivery_location', $data['delivery_location'] ?? null);
        $this->db->bind(':delivery_latitude', $data['delivery_latitude'] ?? null);
        $this->db->bind(':delivery_longitude', $data['delivery_longitude'] ?? null);
        $this->db->bind(':estimated_distance_km', $data['estimated_distance_km'] ?? null);
        $this->db->bind(':estimated_duration_minutes', $data['estimated_duration_minutes'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':created_by', $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            $missionId = $this->db->lastInsertId();

            // Add update to timeline
            $this->addUpdate($missionId, 'status_change', null, 'pending', 'Mission créée');

            return $missionId;
        }

        return false;
    }

    public function updateMission($id, $data) {
        $companyFilter = $this->getCompanyFilter('m');

        $this->db->query("UPDATE missions m SET
            m.mission_type = :mission_type,
            m.client_name = :client_name,
            m.client_phone = :client_phone,
            m.client_email = :client_email,
            m.description = :description,
            m.priority = :priority,
            m.scheduled_start = :scheduled_start,
            m.scheduled_end = :scheduled_end,
            m.assigned_vehicle_id = :assigned_vehicle_id,
            m.assigned_driver_id = :assigned_driver_id,
            m.pickup_location = :pickup_location,
            m.delivery_location = :delivery_location,
            m.estimated_distance_km = :estimated_distance_km,
            m.notes = :notes
            WHERE m.id = :id AND {$companyFilter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':mission_type', $data['mission_type']);
        $this->db->bind(':client_name', $data['client_name']);
        $this->db->bind(':client_phone', $data['client_phone'] ?? null);
        $this->db->bind(':client_email', $data['client_email'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':priority', $data['priority']);
        $this->db->bind(':scheduled_start', $data['scheduled_start'] ?? null);
        $this->db->bind(':scheduled_end', $data['scheduled_end'] ?? null);
        $this->db->bind(':assigned_vehicle_id', $data['assigned_vehicle_id'] ?? null);
        $this->db->bind(':assigned_driver_id', $data['assigned_driver_id'] ?? null);
        $this->db->bind(':pickup_location', $data['pickup_location'] ?? null);
        $this->db->bind(':delivery_location', $data['delivery_location'] ?? null);
        $this->db->bind(':estimated_distance_km', $data['estimated_distance_km'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? null);

        return $this->db->execute();
    }

    public function updateStatus($id, $newStatus) {
        // Get current status
        $mission = $this->getMissionById($id);
        if (!$mission) {
            return false;
        }

        $oldStatus = $mission['status'];
        $companyFilter = $this->getCompanyFilter('m');

        $this->db->query("UPDATE missions m SET m.status = :status WHERE m.id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $newStatus);
        $this->bindCompanyId();

        if ($this->db->execute()) {
            // Update timestamps
            if ($newStatus === 'in_progress') {
                $this->db->query("UPDATE missions m SET m.actual_start = NOW() WHERE m.id = :id AND {$companyFilter}");
                $this->db->bind(':id', $id);
                $this->bindCompanyId();
                $this->db->execute();
            } elseif ($newStatus === 'completed') {
                $this->db->query("UPDATE missions m SET m.actual_end = NOW() WHERE m.id = :id AND {$companyFilter}");
                $this->db->bind(':id', $id);
                $this->bindCompanyId();
                $this->db->execute();
            }

            // Log status change
            $this->addUpdate($id, 'status_change', $oldStatus, $newStatus, "Statut changé de {$oldStatus} à {$newStatus}");

            return true;
        }

        return false;
    }

    // ========================================
    // MISSION ITEMS
    // ========================================

    public function getMissionItems($missionId) {
        $companyFilter = $this->getCompanyFilter('mi');

        $this->db->query("SELECT * FROM mission_items mi WHERE mi.mission_id = :mission_id AND {$companyFilter} ORDER BY mi.created_at");
        $this->db->bind(':mission_id', $missionId);
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function addMissionItem($data) {
        $this->db->query("INSERT INTO mission_items
            (company_id, mission_id, item_name, item_description, quantity, weight_kg, volume_m3, fragile, temperature_controlled, reference_number)
            VALUES (:company_id, :mission_id, :item_name, :item_description, :quantity, :weight_kg, :volume_m3, :fragile, :temperature_controlled, :reference_number)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':item_name', $data['item_name']);
        $this->db->bind(':item_description', $data['item_description'] ?? null);
        $this->db->bind(':quantity', $data['quantity'] ?? 1);
        $this->db->bind(':weight_kg', $data['weight_kg'] ?? null);
        $this->db->bind(':volume_m3', $data['volume_m3'] ?? null);
        $this->db->bind(':fragile', $data['fragile'] ?? 0);
        $this->db->bind(':temperature_controlled', $data['temperature_controlled'] ?? 0);
        $this->db->bind(':reference_number', $data['reference_number'] ?? null);

        return $this->db->execute();
    }

    // ========================================
    // BILLING
    // ========================================

    public function getMissionBilling($missionId) {
        $companyFilter = $this->getCompanyFilter('mb');

        $this->db->query("SELECT * FROM mission_billing mb WHERE mb.mission_id = :mission_id AND {$companyFilter}");
        $this->db->bind(':mission_id', $missionId);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function createBilling($data) {
        // Generate invoice number
        $invoiceNumber = 'INV' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Calculate amounts
        $subtotal = $data['base_rate'] + $data['distance_charge'] + $data['time_charge'] + $data['additional_charges'];
        $taxRate = $data['tax_rate'] ?? 20.00;
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount - ($data['discount_amount'] ?? 0);

        $this->db->query("INSERT INTO mission_billing
            (company_id, mission_id, invoice_number, invoice_date, base_rate, distance_charge, time_charge, additional_charges,
             subtotal, tax_rate, tax_amount, discount_amount, total_amount, payment_status)
            VALUES (:company_id, :mission_id, :invoice_number, :invoice_date, :base_rate, :distance_charge, :time_charge, :additional_charges,
                    :subtotal, :tax_rate, :tax_amount, :discount_amount, :total_amount, 'pending')");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':invoice_number', $invoiceNumber);
        $this->db->bind(':invoice_date', date('Y-m-d'));
        $this->db->bind(':base_rate', $data['base_rate']);
        $this->db->bind(':distance_charge', $data['distance_charge'] ?? 0);
        $this->db->bind(':time_charge', $data['time_charge'] ?? 0);
        $this->db->bind(':additional_charges', $data['additional_charges'] ?? 0);
        $this->db->bind(':subtotal', $subtotal);
        $this->db->bind(':tax_rate', $taxRate);
        $this->db->bind(':tax_amount', $taxAmount);
        $this->db->bind(':discount_amount', $data['discount_amount'] ?? 0);
        $this->db->bind(':total_amount', $totalAmount);

        return $this->db->execute();
    }

    public function updateBillingPayment($billingId, $paymentMethod, $paymentReference = null) {
        $companyFilter = $this->getCompanyFilter('mb');

        $this->db->query("UPDATE mission_billing mb SET
            mb.payment_status = 'paid',
            mb.payment_method = :payment_method,
            mb.payment_date = CURDATE(),
            mb.payment_reference = :payment_reference
            WHERE mb.id = :id AND {$companyFilter}");

        $this->db->bind(':id', $billingId);
        $this->bindCompanyId();
        $this->db->bind(':payment_method', $paymentMethod);
        $this->db->bind(':payment_reference', $paymentReference);

        return $this->db->execute();
    }

    // ========================================
    // UPDATES/TIMELINE
    // ========================================

    public function getMissionUpdates($missionId) {
        $companyFilter = $this->getCompanyFilter('mu');

        $this->db->query("SELECT mu.*,
            CONCAT(u.first_name, ' ', u.last_name) as updated_by_name
            FROM mission_updates mu
            LEFT JOIN users u ON mu.created_by = u.id
            WHERE mu.mission_id = :mission_id AND {$companyFilter}
            ORDER BY mu.created_at DESC");

        $this->db->bind(':mission_id', $missionId);
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function addUpdate($missionId, $updateType, $oldStatus = null, $newStatus = null, $message = null) {
        $this->db->query("INSERT INTO mission_updates
            (company_id, mission_id, update_type, old_status, new_status, message, created_by)
            VALUES (:company_id, :mission_id, :update_type, :old_status, :new_status, :message, :created_by)");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':mission_id', $missionId);
        $this->db->bind(':update_type', $updateType);
        $this->db->bind(':old_status', $oldStatus);
        $this->db->bind(':new_status', $newStatus);
        $this->db->bind(':message', $message);
        $this->db->bind(':created_by', $_SESSION['user_id'] ?? null);

        return $this->db->execute();
    }

    // ========================================
    // STATISTICS
    // ========================================

    public function getDashboardStats() {
        $companyFilter = $this->getCompanyFilter('m');

        $this->db->query("SELECT
            COUNT(*) as total_missions,
            SUM(CASE WHEN m.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN m.status = 'assigned' THEN 1 ELSE 0 END) as assigned_count,
            SUM(CASE WHEN m.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN m.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN m.status = 'completed' AND DATE(m.actual_end) = CURDATE() THEN 1 ELSE 0 END) as completed_today
            FROM missions m
            WHERE {$companyFilter}
            AND m.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $this->bindCompanyId();
        $stats = $this->db->fetch();

        // Revenue stats
        $revenueFilter = $this->getCompanyFilter('mb');
        $this->db->query("SELECT
            SUM(mb.total_amount) as total_revenue,
            SUM(CASE WHEN mb.payment_status = 'paid' THEN mb.total_amount ELSE 0 END) as paid_revenue,
            SUM(CASE WHEN mb.payment_status = 'pending' OR mb.payment_status = 'overdue' THEN mb.total_amount ELSE 0 END) as pending_revenue
            FROM mission_billing mb
            WHERE {$revenueFilter}
            AND mb.invoice_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $this->bindCompanyId();
        $revenue = $this->db->fetch();
        $stats = array_merge($stats, $revenue);

        return $stats;
    }

    public function getUpcomingMissions($days = 7) {
        $companyFilter = $this->getCompanyFilter('m');

        $this->db->query("SELECT m.*,
            v.registration_number,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name
            FROM missions m
            LEFT JOIN vehicles v ON m.assigned_vehicle_id = v.id
            LEFT JOIN drivers d ON m.assigned_driver_id = d.id
            WHERE {$companyFilter}
            AND m.scheduled_start BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :days DAY)
            AND m.status NOT IN ('completed', 'cancelled')
            ORDER BY m.scheduled_start ASC");

        $this->bindCompanyId();
        $this->db->bind(':days', $days);
        return $this->db->fetchAll();
    }

    public function getMissionsByDateRange($startDate, $endDate) {
        $companyFilter = $this->getCompanyFilter('m');

        $this->db->query("SELECT m.*,
            v.registration_number,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name,
            mb.total_amount, mb.payment_status
            FROM missions m
            LEFT JOIN vehicles v ON m.assigned_vehicle_id = v.id
            LEFT JOIN drivers d ON m.assigned_driver_id = d.id
            LEFT JOIN mission_billing mb ON m.id = mb.mission_id
            WHERE {$companyFilter}
            AND DATE(m.scheduled_start) BETWEEN :start_date AND :end_date
            ORDER BY m.scheduled_start ASC");

        $this->bindCompanyId();
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        return $this->db->fetchAll();
    }
}
