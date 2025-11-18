<?php
/**
 * Maintenance Model (GMAO)
 */

class Maintenance extends Database {

    public function __construct() {
        parent::__construct();
    }

    // ========== WORK ORDERS ==========
    public function getAllWorkOrders() {
        $this->query('SELECT wo.*, v.registration_number, v.brand, v.model,
            m.first_name as mechanic_first, m.last_name as mechanic_last,
            mt.name as maintenance_type_name
            FROM work_orders wo
            LEFT JOIN vehicles v ON wo.vehicle_id = v.id
            LEFT JOIN users m ON wo.assigned_to = m.id
            LEFT JOIN maintenance_types mt ON wo.maintenance_type_id = mt.id
            ORDER BY wo.created_at DESC');
        return $this->fetchAll();
    }

    public function getWorkOrderById($id) {
        $this->query('SELECT wo.*, v.registration_number, v.brand, v.model
            FROM work_orders wo
            LEFT JOIN vehicles v ON wo.vehicle_id = v.id
            WHERE wo.id = :id');
        $this->bind(':id', $id);
        return $this->fetch();
    }

    public function addWorkOrder($data) {
        $this->query('INSERT INTO work_orders (
            reference, vehicle_id, maintenance_type_id, type, priority, status,
            description, reported_by, assigned_to, scheduled_date, notes
        ) VALUES (
            :reference, :vehicle_id, :maintenance_type_id, :type, :priority, :status,
            :description, :reported_by, :assigned_to, :scheduled_date, :notes
        )');

        $this->bind(':reference', $data['reference']);
        $this->bind(':vehicle_id', $data['vehicle_id']);
        $this->bind(':maintenance_type_id', $data['maintenance_type_id'] ?? null);
        $this->bind(':type', $data['type']);
        $this->bind(':priority', $data['priority'] ?? 'medium');
        $this->bind(':status', $data['status'] ?? 'pending');
        $this->bind(':description', $data['description']);
        $this->bind(':reported_by', $_SESSION['user_id']);
        $this->bind(':assigned_to', $data['assigned_to'] ?? null);
        $this->bind(':scheduled_date', $data['scheduled_date'] ?? null);
        $this->bind(':notes', $data['notes'] ?? null);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function updateWorkOrder($id, $data) {
        $this->query('UPDATE work_orders SET
            status = :status,
            assigned_to = :assigned_to,
            scheduled_date = :scheduled_date,
            completion_date = :completion_date,
            odometer_at_service = :odometer_at_service,
            labor_cost = :labor_cost,
            parts_cost = :parts_cost,
            total_cost = :total_cost,
            workshop = :workshop,
            notes = :notes
            WHERE id = :id');

        $totalCost = ($data['labor_cost'] ?? 0) + ($data['parts_cost'] ?? 0);

        $this->bind(':id', $id);
        $this->bind(':status', $data['status']);
        $this->bind(':assigned_to', $data['assigned_to'] ?? null);
        $this->bind(':scheduled_date', $data['scheduled_date'] ?? null);
        $this->bind(':completion_date', $data['completion_date'] ?? null);
        $this->bind(':odometer_at_service', $data['odometer_at_service'] ?? null);
        $this->bind(':labor_cost', $data['labor_cost'] ?? 0);
        $this->bind(':parts_cost', $data['parts_cost'] ?? 0);
        $this->bind(':total_cost', $totalCost);
        $this->bind(':workshop', $data['workshop'] ?? null);
        $this->bind(':notes', $data['notes'] ?? null);

        return $this->execute();
    }

    // ========== MAINTENANCE TYPES ==========
    public function getAllMaintenanceTypes() {
        $this->query('SELECT * FROM maintenance_types ORDER BY name');
        return $this->fetchAll();
    }

    public function addMaintenanceType($data) {
        $this->query('INSERT INTO maintenance_types (name, description, category)
            VALUES (:name, :description, :category)');

        $this->bind(':name', $data['name']);
        $this->bind(':description', $data['description'] ?? null);
        $this->bind(':category', $data['category']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    // ========== MAINTENANCE SCHEDULES ==========
    public function getMaintenanceSchedules($vehicleId = null) {
        $sql = 'SELECT ms.*, v.registration_number, v.brand, v.model, mt.name as maintenance_type
            FROM maintenance_schedules ms
            LEFT JOIN vehicles v ON ms.vehicle_id = v.id
            LEFT JOIN maintenance_types mt ON ms.maintenance_type_id = mt.id
            WHERE ms.active = 1';

        if ($vehicleId) {
            $sql .= ' AND ms.vehicle_id = :vehicle_id';
        }

        $sql .= ' ORDER BY ms.next_service_date, ms.next_service_km';

        $this->query($sql);

        if ($vehicleId) {
            $this->bind(':vehicle_id', $vehicleId);
        }

        return $this->fetchAll();
    }

    public function addMaintenanceSchedule($data) {
        $this->query('INSERT INTO maintenance_schedules (
            vehicle_id, maintenance_type_id, schedule_type, interval_km, interval_days,
            last_service_km, last_service_date, next_service_km, next_service_date, active
        ) VALUES (
            :vehicle_id, :maintenance_type_id, :schedule_type, :interval_km, :interval_days,
            :last_service_km, :last_service_date, :next_service_km, :next_service_date, :active
        )');

        $this->bind(':vehicle_id', $data['vehicle_id']);
        $this->bind(':maintenance_type_id', $data['maintenance_type_id']);
        $this->bind(':schedule_type', $data['schedule_type']);
        $this->bind(':interval_km', $data['interval_km'] ?? null);
        $this->bind(':interval_days', $data['interval_days'] ?? null);
        $this->bind(':last_service_km', $data['last_service_km'] ?? null);
        $this->bind(':last_service_date', $data['last_service_date'] ?? null);
        $this->bind(':next_service_km', $data['next_service_km'] ?? null);
        $this->bind(':next_service_date', $data['next_service_date'] ?? null);
        $this->bind(':active', $data['active'] ?? true);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function generateWorkOrderReference() {
        $prefix = 'WO-' . date('Y') . '-';
        $this->query('SELECT COUNT(*) as count FROM work_orders WHERE reference LIKE :prefix');
        $this->bind(':prefix', $prefix . '%');
        $result = $this->fetch();
        return $prefix . str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getDueMaintenances() {
        $this->query('SELECT ms.*, v.registration_number, v.brand, v.model, v.odometer, mt.name as maintenance_type
            FROM maintenance_schedules ms
            LEFT JOIN vehicles v ON ms.vehicle_id = v.id
            LEFT JOIN maintenance_types mt ON ms.maintenance_type_id = mt.id
            WHERE ms.active = 1
            AND (
                (ms.next_service_date IS NOT NULL AND ms.next_service_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
                OR (ms.next_service_km IS NOT NULL AND v.odometer >= ms.next_service_km - 500)
            )
            ORDER BY ms.next_service_date, ms.next_service_km');
        return $this->fetchAll();
    }
}
