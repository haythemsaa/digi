<?php
/**
 * Maintenance Model (GMAO) - Multi-tenant enabled
 */

class Maintenance {
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
    private function getCompanyFilter($tableAlias = 'wo') {
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

    // ========== WORK ORDERS ==========

    public function getAllWorkOrders() {
        $filter = $this->getCompanyFilter('wo');

        $this->db->query("SELECT wo.*, v.registration_number, v.brand, v.model,
            m.first_name as mechanic_first, m.last_name as mechanic_last,
            mt.name as maintenance_type_name
            FROM work_orders wo
            LEFT JOIN vehicles v ON wo.vehicle_id = v.id
            LEFT JOIN users m ON wo.assigned_to = m.id
            LEFT JOIN maintenance_types mt ON wo.maintenance_type_id = mt.id
            WHERE {$filter}
            ORDER BY wo.created_at DESC");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getWorkOrderById($id) {
        $filter = $this->getCompanyFilter('wo');

        $this->db->query("SELECT wo.*, v.registration_number, v.brand, v.model
            FROM work_orders wo
            LEFT JOIN vehicles v ON wo.vehicle_id = v.id
            WHERE wo.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function getWorkOrdersByStatus($status) {
        $filter = $this->getCompanyFilter('wo');

        $this->db->query("SELECT wo.*, v.registration_number, v.brand, v.model
            FROM work_orders wo
            LEFT JOIN vehicles v ON wo.vehicle_id = v.id
            WHERE wo.status = :status AND {$filter}
            ORDER BY wo.created_at DESC");

        $this->db->bind(':status', $status);
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function addWorkOrder($data) {
        $this->db->query('INSERT INTO work_orders (
            company_id, reference, vehicle_id, maintenance_type_id, type, priority, status,
            description, reported_by, assigned_to, scheduled_date, notes
        ) VALUES (
            :company_id, :reference, :vehicle_id, :maintenance_type_id, :type, :priority, :status,
            :description, :reported_by, :assigned_to, :scheduled_date, :notes
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':reference', $data['reference']);
        $this->db->bind(':vehicle_id', $data['vehicle_id']);
        $this->db->bind(':maintenance_type_id', $data['maintenance_type_id'] ?? null);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':priority', $data['priority'] ?? 'medium');
        $this->db->bind(':status', $data['status'] ?? 'pending');
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':reported_by', $_SESSION['user_id']);
        $this->db->bind(':assigned_to', $data['assigned_to'] ?? null);
        $this->db->bind(':scheduled_date', $data['scheduled_date'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateWorkOrder($id, $data) {
        $filter = $this->getCompanyFilter('wo');

        $this->db->query("UPDATE work_orders wo SET
            wo.status = :status,
            wo.assigned_to = :assigned_to,
            wo.scheduled_date = :scheduled_date,
            wo.completion_date = :completion_date,
            wo.odometer_at_service = :odometer_at_service,
            wo.labor_cost = :labor_cost,
            wo.parts_cost = :parts_cost,
            wo.total_cost = :total_cost,
            wo.workshop = :workshop,
            wo.notes = :notes
            WHERE wo.id = :id AND {$filter}");

        $totalCost = ($data['labor_cost'] ?? 0) + ($data['parts_cost'] ?? 0);

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':assigned_to', $data['assigned_to'] ?? null);
        $this->db->bind(':scheduled_date', $data['scheduled_date'] ?? null);
        $this->db->bind(':completion_date', $data['completion_date'] ?? null);
        $this->db->bind(':odometer_at_service', $data['odometer_at_service'] ?? null);
        $this->db->bind(':labor_cost', $data['labor_cost'] ?? 0);
        $this->db->bind(':parts_cost', $data['parts_cost'] ?? 0);
        $this->db->bind(':total_cost', $totalCost);
        $this->db->bind(':workshop', $data['workshop'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? null);

        return $this->db->execute();
    }

    public function deleteWorkOrder($id) {
        $filter = $this->getCompanyFilter('wo');

        $this->db->query("DELETE FROM work_orders WHERE id = :id AND {$filter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();

        return $this->db->execute();
    }

    // ========== MAINTENANCE TYPES ==========

    public function getAllMaintenanceTypes() {
        $filter = $this->getCompanyFilter('mt');

        $this->db->query("SELECT * FROM maintenance_types mt
            WHERE {$filter}
            ORDER BY mt.name");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function addMaintenanceType($data) {
        $this->db->query('INSERT INTO maintenance_types (company_id, name, description, category)
            VALUES (:company_id, :name, :description, :category)');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':category', $data['category']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // ========== MAINTENANCE SCHEDULES ==========

    public function getMaintenanceSchedules($vehicleId = null) {
        $filter = $this->getCompanyFilter('ms');

        $sql = "SELECT ms.*, v.registration_number, v.brand, v.model, mt.name as maintenance_type
            FROM maintenance_schedules ms
            LEFT JOIN vehicles v ON ms.vehicle_id = v.id
            LEFT JOIN maintenance_types mt ON ms.maintenance_type_id = mt.id
            WHERE {$filter} AND ms.active = 1";

        if ($vehicleId) {
            $sql .= ' AND ms.vehicle_id = :vehicle_id';
        }

        $sql .= ' ORDER BY ms.next_service_date, ms.next_service_km';

        $this->db->query($sql);
        $this->bindCompanyId();

        if ($vehicleId) {
            $this->db->bind(':vehicle_id', $vehicleId);
        }

        return $this->db->fetchAll();
    }

    public function addMaintenanceSchedule($data) {
        $this->db->query('INSERT INTO maintenance_schedules (
            company_id, vehicle_id, maintenance_type_id, schedule_type, interval_km, interval_days,
            last_service_km, last_service_date, next_service_km, next_service_date, active
        ) VALUES (
            :company_id, :vehicle_id, :maintenance_type_id, :schedule_type, :interval_km, :interval_days,
            :last_service_km, :last_service_date, :next_service_km, :next_service_date, :active
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':vehicle_id', $data['vehicle_id']);
        $this->db->bind(':maintenance_type_id', $data['maintenance_type_id']);
        $this->db->bind(':schedule_type', $data['schedule_type']);
        $this->db->bind(':interval_km', $data['interval_km'] ?? null);
        $this->db->bind(':interval_days', $data['interval_days'] ?? null);
        $this->db->bind(':last_service_km', $data['last_service_km'] ?? null);
        $this->db->bind(':last_service_date', $data['last_service_date'] ?? null);
        $this->db->bind(':next_service_km', $data['next_service_km'] ?? null);
        $this->db->bind(':next_service_date', $data['next_service_date'] ?? null);
        $this->db->bind(':active', $data['active'] ?? true);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateMaintenanceSchedule($id, $data) {
        $filter = $this->getCompanyFilter('ms');

        $this->db->query("UPDATE maintenance_schedules ms SET
            ms.last_service_km = :last_service_km,
            ms.last_service_date = :last_service_date,
            ms.next_service_km = :next_service_km,
            ms.next_service_date = :next_service_date,
            ms.active = :active
            WHERE ms.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':last_service_km', $data['last_service_km'] ?? null);
        $this->db->bind(':last_service_date', $data['last_service_date'] ?? null);
        $this->db->bind(':next_service_km', $data['next_service_km'] ?? null);
        $this->db->bind(':next_service_date', $data['next_service_date'] ?? null);
        $this->db->bind(':active', $data['active'] ?? true);

        return $this->db->execute();
    }

    public function generateWorkOrderReference() {
        $filter = $this->getCompanyFilter('wo');

        $prefix = 'WO-' . date('Y') . '-';
        $this->db->query("SELECT COUNT(*) as count FROM work_orders wo
            WHERE wo.reference LIKE :prefix AND {$filter}");

        $this->db->bind(':prefix', $prefix . '%');
        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $prefix . str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getDueMaintenances() {
        $filter = $this->getCompanyFilter('ms');

        $this->db->query("SELECT ms.*, v.registration_number, v.brand, v.model, v.odometer, mt.name as maintenance_type
            FROM maintenance_schedules ms
            LEFT JOIN vehicles v ON ms.vehicle_id = v.id
            LEFT JOIN maintenance_types mt ON ms.maintenance_type_id = mt.id
            WHERE {$filter}
            AND ms.active = 1
            AND (
                (ms.next_service_date IS NOT NULL AND ms.next_service_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY))
                OR (ms.next_service_km IS NOT NULL AND v.odometer >= ms.next_service_km - 500)
            )
            ORDER BY ms.next_service_date, ms.next_service_km");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    // ========== DASHBOARD & STATS ==========

    public function getDashboardStats() {
        $filter = $this->getCompanyFilter('wo');

        $this->db->query("SELECT
            COUNT(*) as total_work_orders,
            SUM(CASE WHEN wo.status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN wo.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_orders,
            SUM(CASE WHEN wo.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
            SUM(wo.total_cost) as total_maintenance_cost,
            AVG(wo.total_cost) as avg_cost_per_order
            FROM work_orders wo
            WHERE {$filter}
            AND wo.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function getMaintenanceCostsByMonth($months = 12) {
        $filter = $this->getCompanyFilter('wo');

        $this->db->query("SELECT
            DATE_FORMAT(wo.created_at, '%Y-%m') as month,
            COUNT(*) as work_orders,
            SUM(wo.total_cost) as total_cost,
            AVG(wo.total_cost) as avg_cost
            FROM work_orders wo
            WHERE {$filter}
            AND wo.created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY month
            ORDER BY month ASC");

        $this->bindCompanyId();
        $this->db->bind(':months', $months);
        return $this->db->fetchAll();
    }

    public function countWorkOrders() {
        $filter = $this->getCompanyFilter('wo');

        $this->db->query("SELECT COUNT(*) as total FROM work_orders wo WHERE {$filter}");
        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $result['total'];
    }
}
