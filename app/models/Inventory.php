<?php
/**
 * Inventory Model
 */

class Inventory extends Database {

    public function __construct() {
        parent::__construct();
    }

    public function getAllParts() {
        $this->query('SELECT p.*, s.company_name as supplier_name
            FROM parts p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            ORDER BY p.part_name');
        return $this->fetchAll();
    }

    public function getPartById($id) {
        $this->query('SELECT * FROM parts WHERE id = :id');
        $this->bind(':id', $id);
        return $this->fetch();
    }

    public function addPart($data) {
        $this->query('INSERT INTO parts (
            part_number, part_name, description, category, unit, min_stock,
            current_stock, unit_cost, selling_price, location, supplier_id, status
        ) VALUES (
            :part_number, :part_name, :description, :category, :unit, :min_stock,
            :current_stock, :unit_cost, :selling_price, :location, :supplier_id, :status
        )');

        $this->bind(':part_number', $data['part_number']);
        $this->bind(':part_name', $data['part_name']);
        $this->bind(':description', $data['description'] ?? null);
        $this->bind(':category', $data['category']);
        $this->bind(':unit', $data['unit'] ?? 'piece');
        $this->bind(':min_stock', $data['min_stock'] ?? 0);
        $this->bind(':current_stock', $data['current_stock'] ?? 0);
        $this->bind(':unit_cost', $data['unit_cost'] ?? null);
        $this->bind(':selling_price', $data['selling_price'] ?? null);
        $this->bind(':location', $data['location'] ?? null);
        $this->bind(':supplier_id', $data['supplier_id'] ?? null);
        $this->bind(':status', $data['status'] ?? 'active');

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function addStockMovement($data) {
        $this->query('INSERT INTO stock_movements (
            part_id, movement_type, quantity, reference_type, reference_id,
            unit_cost, notes, created_by
        ) VALUES (
            :part_id, :movement_type, :quantity, :reference_type, :reference_id,
            :unit_cost, :notes, :created_by
        )');

        $this->bind(':part_id', $data['part_id']);
        $this->bind(':movement_type', $data['movement_type']);
        $this->bind(':quantity', $data['quantity']);
        $this->bind(':reference_type', $data['reference_type'] ?? null);
        $this->bind(':reference_id', $data['reference_id'] ?? null);
        $this->bind(':unit_cost', $data['unit_cost'] ?? null);
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            // Update part stock
            $multiplier = $data['movement_type'] === 'in' ? 1 : -1;
            $this->query('UPDATE parts SET current_stock = current_stock + :quantity WHERE id = :part_id');
            $this->bind(':quantity', $data['quantity'] * $multiplier);
            $this->bind(':part_id', $data['part_id']);
            $this->execute();

            return $this->lastInsertId();
        }
        return false;
    }

    public function getLowStockParts() {
        $this->query('SELECT * FROM parts WHERE current_stock <= min_stock AND status = "active" ORDER BY part_name');
        return $this->fetchAll();
    }

    public function getStockMovements($partId = null, $limit = 100) {
        $sql = 'SELECT sm.*, p.part_name, u.first_name, u.last_name
            FROM stock_movements sm
            LEFT JOIN parts p ON sm.part_id = p.id
            LEFT JOIN users u ON sm.created_by = u.id';

        if ($partId) {
            $sql .= ' WHERE sm.part_id = :part_id';
        }

        $sql .= ' ORDER BY sm.created_at DESC LIMIT :limit';

        $this->query($sql);

        if ($partId) {
            $this->bind(':part_id', $partId);
        }

        $this->bind(':limit', $limit);
        return $this->fetchAll();
    }
}
