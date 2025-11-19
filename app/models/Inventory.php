<?php
/**
 * Inventory Model - Multi-tenant enabled
 */

class Inventory {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();

        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    private function getCompanyFilter($tableAlias = 'p') {
        if (isSuperAdmin()) {
            return '1=1';
        }
        return "{$tableAlias}.company_id = :company_id";
    }

    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    // ========== PARTS ==========

    public function getAllParts() {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT p.*, s.company_name as supplier_name
            FROM parts p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE {$filter}
            ORDER BY p.part_name");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getPartById($id) {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT * FROM parts p WHERE p.id = :id AND {$filter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();

        return $this->db->fetch();
    }

    public function getPartsByCategory($category) {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT p.* FROM parts p
            WHERE p.category = :category AND {$filter}
            ORDER BY p.part_name");

        $this->db->bind(':category', $category);
        $this->bindCompanyId();

        return $this->db->fetchAll();
    }

    public function searchParts($keyword) {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT p.* FROM parts p
            WHERE {$filter}
            AND (p.part_name LIKE :keyword
               OR p.part_number LIKE :keyword
               OR p.description LIKE :keyword)
            ORDER BY p.part_name");

        $this->bindCompanyId();
        $this->db->bind(':keyword', '%' . $keyword . '%');

        return $this->db->fetchAll();
    }

    public function addPart($data) {
        $this->db->query('INSERT INTO parts (
            company_id, part_number, part_name, description, category, unit, min_stock,
            current_stock, unit_cost, selling_price, location, supplier_id, status
        ) VALUES (
            :company_id, :part_number, :part_name, :description, :category, :unit, :min_stock,
            :current_stock, :unit_cost, :selling_price, :location, :supplier_id, :status
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':part_number', $data['part_number']);
        $this->db->bind(':part_name', $data['part_name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':unit', $data['unit'] ?? 'piece');
        $this->db->bind(':min_stock', $data['min_stock'] ?? 0);
        $this->db->bind(':current_stock', $data['current_stock'] ?? 0);
        $this->db->bind(':unit_cost', $data['unit_cost'] ?? null);
        $this->db->bind(':selling_price', $data['selling_price'] ?? null);
        $this->db->bind(':location', $data['location'] ?? null);
        $this->db->bind(':supplier_id', $data['supplier_id'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updatePart($id, $data) {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("UPDATE parts p SET
            p.part_name = :part_name,
            p.description = :description,
            p.category = :category,
            p.unit = :unit,
            p.min_stock = :min_stock,
            p.unit_cost = :unit_cost,
            p.selling_price = :selling_price,
            p.location = :location,
            p.supplier_id = :supplier_id,
            p.status = :status
            WHERE p.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':part_name', $data['part_name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':unit', $data['unit'] ?? 'piece');
        $this->db->bind(':min_stock', $data['min_stock'] ?? 0);
        $this->db->bind(':unit_cost', $data['unit_cost'] ?? null);
        $this->db->bind(':selling_price', $data['selling_price'] ?? null);
        $this->db->bind(':location', $data['location'] ?? null);
        $this->db->bind(':supplier_id', $data['supplier_id'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');

        return $this->db->execute();
    }

    // ========== STOCK MOVEMENTS ==========

    public function addStockMovement($data) {
        // Verify part belongs to company
        $part = $this->getPartById($data['part_id']);
        if (!$part) {
            return false;
        }

        $this->db->query('INSERT INTO stock_movements (
            part_id, movement_type, quantity, reference_type, reference_id,
            unit_cost, notes, created_by
        ) VALUES (
            :part_id, :movement_type, :quantity, :reference_type, :reference_id,
            :unit_cost, :notes, :created_by
        )');

        $this->db->bind(':part_id', $data['part_id']);
        $this->db->bind(':movement_type', $data['movement_type']);
        $this->db->bind(':quantity', $data['quantity']);
        $this->db->bind(':reference_type', $data['reference_type'] ?? null);
        $this->db->bind(':reference_id', $data['reference_id'] ?? null);
        $this->db->bind(':unit_cost', $data['unit_cost'] ?? null);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':created_by', $_SESSION['user_id']);

        if ($this->db->execute()) {
            // Update part stock
            $multiplier = $data['movement_type'] === 'in' ? 1 : -1;

            $filter = $this->getCompanyFilter('p');
            $this->db->query("UPDATE parts p SET p.current_stock = p.current_stock + :quantity
                WHERE p.id = :part_id AND {$filter}");

            $this->db->bind(':quantity', $data['quantity'] * $multiplier);
            $this->db->bind(':part_id', $data['part_id']);
            $this->bindCompanyId();
            $this->db->execute();

            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getStockMovements($partId = null, $limit = 100) {
        $filter = $this->getCompanyFilter('p');

        $sql = "SELECT sm.*, p.part_name, u.first_name, u.last_name
            FROM stock_movements sm
            LEFT JOIN parts p ON sm.part_id = p.id
            LEFT JOIN users u ON sm.created_by = u.id
            WHERE {$filter}";

        if ($partId) {
            $sql .= ' AND sm.part_id = :part_id';
        }

        $sql .= ' ORDER BY sm.created_at DESC LIMIT :limit';

        $this->db->query($sql);
        $this->bindCompanyId();

        if ($partId) {
            $this->db->bind(':part_id', $partId);
        }

        $this->db->bind(':limit', $limit);
        return $this->db->fetchAll();
    }

    // ========== INVENTORY ANALYSIS ==========

    public function getLowStockParts() {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT * FROM parts p
            WHERE p.current_stock <= p.min_stock
            AND p.status = 'active'
            AND {$filter}
            ORDER BY p.part_name");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getOutOfStockParts() {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT * FROM parts p
            WHERE p.current_stock = 0
            AND p.status = 'active'
            AND {$filter}
            ORDER BY p.part_name");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getInventoryValue() {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT
            SUM(p.current_stock * p.unit_cost) as total_value,
            COUNT(*) as total_parts,
            SUM(p.current_stock) as total_items
            FROM parts p
            WHERE {$filter} AND p.status = 'active'");

        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function getDashboardStats() {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT
            COUNT(*) as total_parts,
            SUM(p.current_stock) as total_stock,
            SUM(p.current_stock * p.unit_cost) as inventory_value,
            SUM(CASE WHEN p.current_stock <= p.min_stock THEN 1 ELSE 0 END) as low_stock_count,
            SUM(CASE WHEN p.current_stock = 0 THEN 1 ELSE 0 END) as out_of_stock_count
            FROM parts p
            WHERE {$filter} AND p.status = 'active'");

        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function getStockMovementsByPeriod($months = 6) {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT
            DATE_FORMAT(sm.created_at, '%Y-%m') as month,
            sm.movement_type,
            SUM(sm.quantity) as total_quantity,
            COUNT(*) as movement_count
            FROM stock_movements sm
            LEFT JOIN parts p ON sm.part_id = p.id
            WHERE {$filter}
            AND sm.created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY month, sm.movement_type
            ORDER BY month ASC");

        $this->bindCompanyId();
        $this->db->bind(':months', $months);

        return $this->db->fetchAll();
    }

    public function countParts() {
        $filter = $this->getCompanyFilter('p');

        $this->db->query("SELECT COUNT(*) as total FROM parts p WHERE {$filter}");
        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $result['total'];
    }
}
