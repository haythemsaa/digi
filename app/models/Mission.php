<?php

class Mission {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // ========================================
    // MISSIONS CRUD
    // ========================================

    public function getAllMissions($status = null, $limit = 100) {
        $sql = "SELECT m.*,
            v.registration_number,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name,
            mb.total_amount, mb.payment_status
            FROM missions m
            LEFT JOIN vehicles v ON m.assigned_vehicle_id = v.id
            LEFT JOIN drivers d ON m.assigned_driver_id = d.id
            LEFT JOIN mission_billing mb ON m.id = mb.mission_id";

        if ($status) {
            $sql .= " WHERE m.status = :status";
        }

        $sql .= " ORDER BY m.created_at DESC LIMIT :limit";

        $this->db->query($sql);

        if ($status) {
            $this->db->bind(':status', $status);
        }

        $this->db->bind(':limit', $limit);
        return $this->db->fetchAll();
    }

    public function getMissionById($id) {
        $this->db->query("SELECT m.*,
            v.registration_number, v.brand, v.model,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name,
            d.phone as driver_phone
            FROM missions m
            LEFT JOIN vehicles v ON m.assigned_vehicle_id = v.id
            LEFT JOIN drivers d ON m.assigned_driver_id = d.id
            WHERE m.id = :id");

        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    public function createMission($data) {
        // Generate mission number
        $missionNumber = 'MSN' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $this->db->query("INSERT INTO missions
            (mission_number, mission_type, client_name, client_phone, client_email, client_address,
             description, priority, status, scheduled_start, scheduled_end,
             assigned_vehicle_id, assigned_driver_id,
             pickup_location, pickup_latitude, pickup_longitude,
             delivery_location, delivery_latitude, delivery_longitude,
             estimated_distance_km, estimated_duration_minutes, notes, created_by)
            VALUES (:mission_number, :mission_type, :client_name, :client_phone, :client_email, :client_address,
                    :description, :priority, :status, :scheduled_start, :scheduled_end,
                    :assigned_vehicle_id, :assigned_driver_id,
                    :pickup_location, :pickup_latitude, :pickup_longitude,
                    :delivery_location, :delivery_latitude, :delivery_longitude,
                    :estimated_distance_km, :estimated_duration_minutes, :notes, :created_by)");

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
        $this->db->query("UPDATE missions SET
            mission_type = :mission_type,
            client_name = :client_name,
            client_phone = :client_phone,
            client_email = :client_email,
            description = :description,
            priority = :priority,
            scheduled_start = :scheduled_start,
            scheduled_end = :scheduled_end,
            assigned_vehicle_id = :assigned_vehicle_id,
            assigned_driver_id = :assigned_driver_id,
            pickup_location = :pickup_location,
            delivery_location = :delivery_location,
            estimated_distance_km = :estimated_distance_km,
            notes = :notes
            WHERE id = :id");

        $this->db->bind(':id', $id);
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
        $oldStatus = $mission['status'];

        $this->db->query("UPDATE missions SET status = :status WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $newStatus);

        if ($this->db->execute()) {
            // Update timestamps
            if ($newStatus === 'in_progress') {
                $this->db->query("UPDATE missions SET actual_start = NOW() WHERE id = :id");
                $this->db->bind(':id', $id);
                $this->db->execute();
            } elseif ($newStatus === 'completed') {
                $this->db->query("UPDATE missions SET actual_end = NOW() WHERE id = :id");
                $this->db->bind(':id', $id);
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
        $this->db->query("SELECT * FROM mission_items WHERE mission_id = :mission_id ORDER BY created_at");
        $this->db->bind(':mission_id', $missionId);
        return $this->db->fetchAll();
    }

    public function addMissionItem($data) {
        $this->db->query("INSERT INTO mission_items
            (mission_id, item_name, item_description, quantity, weight_kg, volume_m3, fragile, temperature_controlled, reference_number)
            VALUES (:mission_id, :item_name, :item_description, :quantity, :weight_kg, :volume_m3, :fragile, :temperature_controlled, :reference_number)");

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
        $this->db->query("SELECT * FROM mission_billing WHERE mission_id = :mission_id");
        $this->db->bind(':mission_id', $missionId);
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
            (mission_id, invoice_number, invoice_date, base_rate, distance_charge, time_charge, additional_charges,
             subtotal, tax_rate, tax_amount, discount_amount, total_amount, payment_status)
            VALUES (:mission_id, :invoice_number, :invoice_date, :base_rate, :distance_charge, :time_charge, :additional_charges,
                    :subtotal, :tax_rate, :tax_amount, :discount_amount, :total_amount, 'pending')");

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
        $this->db->query("UPDATE mission_billing SET
            payment_status = 'paid',
            payment_method = :payment_method,
            payment_date = CURDATE(),
            payment_reference = :payment_reference
            WHERE id = :id");

        $this->db->bind(':id', $billingId);
        $this->db->bind(':payment_method', $paymentMethod);
        $this->db->bind(':payment_reference', $paymentReference);

        return $this->db->execute();
    }

    // ========================================
    // UPDATES/TIMELINE
    // ========================================

    public function getMissionUpdates($missionId) {
        $this->db->query("SELECT mu.*,
            CONCAT(u.first_name, ' ', u.last_name) as updated_by_name
            FROM mission_updates mu
            LEFT JOIN users u ON mu.created_by = u.id
            WHERE mu.mission_id = :mission_id
            ORDER BY mu.created_at DESC");

        $this->db->bind(':mission_id', $missionId);
        return $this->db->fetchAll();
    }

    public function addUpdate($missionId, $updateType, $oldStatus = null, $newStatus = null, $message = null) {
        $this->db->query("INSERT INTO mission_updates
            (mission_id, update_type, old_status, new_status, message, created_by)
            VALUES (:mission_id, :update_type, :old_status, :new_status, :message, :created_by)");

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
        $this->db->query("SELECT
            COUNT(*) as total_missions,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'completed' AND DATE(actual_end) = CURDATE() THEN 1 ELSE 0 END) as completed_today
            FROM missions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $stats = $this->db->fetch();

        // Revenue stats
        $this->db->query("SELECT
            SUM(total_amount) as total_revenue,
            SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_revenue,
            SUM(CASE WHEN payment_status = 'pending' OR payment_status = 'overdue' THEN total_amount ELSE 0 END) as pending_revenue
            FROM mission_billing
            WHERE invoice_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $revenue = $this->db->fetch();
        $stats = array_merge($stats, $revenue);

        return $stats;
    }

    public function getUpcomingMissions($days = 7) {
        $this->db->query("SELECT m.*,
            v.registration_number,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name
            FROM missions m
            LEFT JOIN vehicles v ON m.assigned_vehicle_id = v.id
            LEFT JOIN drivers d ON m.assigned_driver_id = d.id
            WHERE m.scheduled_start BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :days DAY)
            AND m.status NOT IN ('completed', 'cancelled')
            ORDER BY m.scheduled_start ASC");

        $this->db->bind(':days', $days);
        return $this->db->fetchAll();
    }

    public function getMissionsByDateRange($startDate, $endDate) {
        $this->db->query("SELECT m.*,
            v.registration_number,
            CONCAT(d.first_name, ' ', d.last_name) as driver_name,
            mb.total_amount, mb.payment_status
            FROM missions m
            LEFT JOIN vehicles v ON m.assigned_vehicle_id = v.id
            LEFT JOIN drivers d ON m.assigned_driver_id = d.id
            LEFT JOIN mission_billing mb ON m.id = mb.mission_id
            WHERE DATE(m.scheduled_start) BETWEEN :start_date AND :end_date
            ORDER BY m.scheduled_start ASC");

        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        return $this->db->fetchAll();
    }
}
