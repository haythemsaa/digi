<?php
/**
 * Stock Document Model
 * Manages advanced stock operations with formal documents
 */

class StockDocument extends Model {

    /**
     * Get all stock locations
     */
    public function getAllLocations() {
        $this->db->query("SELECT * FROM stock_locations WHERE is_active = 1 ORDER BY name");
        return $this->db->resultSet();
    }

    /**
     * Get location by ID
     */
    public function getLocationById($id) {
        $this->db->query("SELECT * FROM stock_locations WHERE id = ?");
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    /**
     * Create stock location
     */
    public function createLocation($data) {
        $this->db->query("INSERT INTO stock_locations
                         (name, type, address, manager_id, capacity, is_active)
                         VALUES (?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $data['name']);
        $this->db->bind(2, $data['type']);
        $this->db->bind(3, $data['address'] ?? null);
        $this->db->bind(4, $data['manager_id'] ?? null);
        $this->db->bind(5, $data['capacity'] ?? null);
        $this->db->bind(6, $data['is_active'] ?? 1);

        return $this->db->execute();
    }

    /**
     * Update stock location
     */
    public function updateLocation($id, $data) {
        $this->db->query("UPDATE stock_locations SET
                         name = ?, type = ?, address = ?,
                         manager_id = ?, capacity = ?, is_active = ?
                         WHERE id = ?");

        $this->db->bind(1, $data['name']);
        $this->db->bind(2, $data['type']);
        $this->db->bind(3, $data['address'] ?? null);
        $this->db->bind(4, $data['manager_id'] ?? null);
        $this->db->bind(5, $data['capacity'] ?? null);
        $this->db->bind(6, $data['is_active'] ?? 1);
        $this->db->bind(7, $id);

        return $this->db->execute();
    }

    // ==================== STOCK DOCUMENTS ====================

    /**
     * Get all stock documents
     */
    public function getAllDocuments($filters = []) {
        $sql = "SELECT sd.*,
                       sl1.name as source_location_name,
                       sl2.name as destination_location_name,
                       CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM stock_documents sd
                LEFT JOIN stock_locations sl1 ON sd.source_location_id = sl1.id
                LEFT JOIN stock_locations sl2 ON sd.destination_location_id = sl2.id
                LEFT JOIN users u ON sd.created_by = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['document_type'])) {
            $sql .= " AND sd.document_type = ?";
            $params[] = $filters['document_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND sd.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['location_id'])) {
            $sql .= " AND (sd.source_location_id = ? OR sd.destination_location_id = ?)";
            $params[] = $filters['location_id'];
            $params[] = $filters['location_id'];
        }

        $sql .= " ORDER BY sd.document_date DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get document by ID with items
     */
    public function getDocumentById($id) {
        $this->db->query("SELECT sd.*,
                                 sl1.name as source_location_name,
                                 sl2.name as destination_location_name,
                                 CONCAT(u1.first_name, ' ', u1.last_name) as created_by_name,
                                 CONCAT(u2.first_name, ' ', u2.last_name) as validated_by_name
                          FROM stock_documents sd
                          LEFT JOIN stock_locations sl1 ON sd.source_location_id = sl1.id
                          LEFT JOIN stock_locations sl2 ON sd.destination_location_id = sl2.id
                          LEFT JOIN users u1 ON sd.created_by = u1.id
                          LEFT JOIN users u2 ON sd.validated_by = u2.id
                          WHERE sd.id = ?");
        $this->db->bind(1, $id);
        $document = $this->db->single();

        if ($document) {
            $document['items'] = $this->getDocumentItems($id);
        }

        return $document;
    }

    /**
     * Get document items
     */
    public function getDocumentItems($documentId) {
        $this->db->query("SELECT sdi.*, p.name as part_name, p.reference as part_reference
                         FROM stock_document_items sdi
                         LEFT JOIN parts p ON sdi.part_id = p.id
                         WHERE sdi.document_id = ?");
        $this->db->bind(1, $documentId);
        return $this->db->resultSet();
    }

    /**
     * Create stock document
     */
    public function createDocument($data, $items) {
        // Generate document number
        $documentNumber = $this->generateDocumentNumber($data['document_type']);

        $this->db->query("INSERT INTO stock_documents
                         (document_number, document_type, document_date,
                          source_location_id, destination_location_id,
                          reference, notes, status, created_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', ?)");

        $this->db->bind(1, $documentNumber);
        $this->db->bind(2, $data['document_type']);
        $this->db->bind(3, $data['document_date']);
        $this->db->bind(4, $data['source_location_id'] ?? null);
        $this->db->bind(5, $data['destination_location_id'] ?? null);
        $this->db->bind(6, $data['reference'] ?? null);
        $this->db->bind(7, $data['notes'] ?? null);
        $this->db->bind(8, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            $documentId = $this->db->lastInsertId();

            // Add items
            foreach ($items as $item) {
                $this->addDocumentItem($documentId, $item);
            }

            return $documentId;
        }

        return false;
    }

    /**
     * Add document item
     */
    private function addDocumentItem($documentId, $item) {
        $this->db->query("INSERT INTO stock_document_items
                         (document_id, part_id, quantity, unit_cost, notes)
                         VALUES (?, ?, ?, ?, ?)");

        $this->db->bind(1, $documentId);
        $this->db->bind(2, $item['part_id']);
        $this->db->bind(3, $item['quantity']);
        $this->db->bind(4, $item['unit_cost'] ?? 0);
        $this->db->bind(5, $item['notes'] ?? null);

        return $this->db->execute();
    }

    /**
     * Validate document and update stock
     */
    public function validateDocument($id) {
        // Get document
        $document = $this->getDocumentById($id);

        if (!$document || $document['status'] != 'draft') {
            return false;
        }

        // Update document status
        $this->db->query("UPDATE stock_documents
                         SET status = 'validated',
                             validated_by = ?,
                             validated_at = NOW()
                         WHERE id = ?");
        $this->db->bind(1, $_SESSION['user_id'] ?? null);
        $this->db->bind(2, $id);

        if (!$this->db->execute()) {
            return false;
        }

        // Update stock based on document type
        return $this->processStockMovements($document);
    }

    /**
     * Process stock movements based on document type
     */
    private function processStockMovements($document) {
        $items = $document['items'];

        foreach ($items as $item) {
            switch ($document['document_type']) {
                case 'receipt':
                    // Add to destination location
                    $this->updatePartStock($item['part_id'], $item['quantity'], $document['destination_location_id']);
                    break;

                case 'issue':
                    // Remove from source location
                    $this->updatePartStock($item['part_id'], -$item['quantity'], $document['source_location_id']);
                    break;

                case 'transfer':
                    // Remove from source
                    $this->updatePartStock($item['part_id'], -$item['quantity'], $document['source_location_id']);
                    // Add to destination
                    $this->updatePartStock($item['part_id'], $item['quantity'], $document['destination_location_id']);
                    break;

                case 'adjustment':
                    // Adjust stock in location
                    $locationId = $document['source_location_id'] ?? $document['destination_location_id'];
                    // Note: For adjustments, quantity can be negative or positive
                    $this->setPartStock($item['part_id'], $item['quantity'], $locationId);
                    break;

                case 'return':
                    // Add back to source location
                    $this->updatePartStock($item['part_id'], $item['quantity'], $document['source_location_id']);
                    break;
            }
        }

        return true;
    }

    /**
     * Update part stock quantity
     */
    private function updatePartStock($partId, $quantityChange, $locationId = null) {
        // Update main parts table
        $this->db->query("UPDATE parts SET quantity = quantity + ? WHERE id = ?");
        $this->db->bind(1, $quantityChange);
        $this->db->bind(2, $partId);
        $this->db->execute();

        // TODO: Update location-specific stock if needed
        // This would require a parts_stock_locations table for multi-location inventory
    }

    /**
     * Set part stock quantity (for adjustments)
     */
    private function setPartStock($partId, $newQuantity, $locationId = null) {
        $this->db->query("UPDATE parts SET quantity = ? WHERE id = ?");
        $this->db->bind(1, $newQuantity);
        $this->db->bind(2, $partId);
        $this->db->execute();
    }

    /**
     * Generate document number based on type
     */
    private function generateDocumentNumber($type) {
        $year = date('Y');
        $prefixes = [
            'receipt' => 'RCP',
            'issue' => 'ISS',
            'transfer' => 'TRF',
            'adjustment' => 'ADJ',
            'return' => 'RET'
        ];

        $prefix = ($prefixes[$type] ?? 'DOC') . '-' . $year . '-';

        $this->db->query("SELECT document_number FROM stock_documents
                         WHERE document_number LIKE ?
                         ORDER BY document_number DESC LIMIT 1");
        $this->db->bind(1, $prefix . '%');
        $result = $this->db->single();

        if ($result) {
            $lastNumber = intval(substr($result['document_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Cancel document
     */
    public function cancelDocument($id, $reason = null) {
        $this->db->query("UPDATE stock_documents SET status = 'cancelled', notes = CONCAT(notes, '\nCancellation reason: ', ?) WHERE id = ?");
        $this->db->bind(1, $reason ?? 'No reason provided');
        $this->db->bind(2, $id);
        return $this->db->execute();
    }

    // ==================== PHYSICAL INVENTORIES ====================

    /**
     * Get all inventories
     */
    public function getAllInventories($filters = []) {
        $sql = "SELECT pi.*,
                       sl.name as location_name,
                       CONCAT(u1.first_name, ' ', u1.last_name) as created_by_name,
                       CONCAT(u2.first_name, ' ', u2.last_name) as validated_by_name
                FROM physical_inventories pi
                LEFT JOIN stock_locations sl ON pi.location_id = sl.id
                LEFT JOIN users u1 ON pi.created_by = u1.id
                LEFT JOIN users u2 ON pi.validated_by = u2.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND pi.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['location_id'])) {
            $sql .= " AND pi.location_id = ?";
            $params[] = $filters['location_id'];
        }

        $sql .= " ORDER BY pi.inventory_date DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get inventory by ID
     */
    public function getInventoryById($id) {
        $this->db->query("SELECT pi.*,
                                 sl.name as location_name,
                                 CONCAT(u1.first_name, ' ', u1.last_name) as created_by_name,
                                 CONCAT(u2.first_name, ' ', u2.last_name) as validated_by_name
                          FROM physical_inventories pi
                          LEFT JOIN stock_locations sl ON pi.location_id = sl.id
                          LEFT JOIN users u1 ON pi.created_by = u1.id
                          LEFT JOIN users u2 ON pi.validated_by = u2.id
                          WHERE pi.id = ?");
        $this->db->bind(1, $id);
        $inventory = $this->db->single();

        if ($inventory) {
            $inventory['items'] = $this->getInventoryItems($id);
        }

        return $inventory;
    }

    /**
     * Get inventory items
     */
    public function getInventoryItems($inventoryId) {
        $this->db->query("SELECT ii.*, p.name as part_name, p.reference as part_reference
                         FROM inventory_items ii
                         LEFT JOIN parts p ON ii.part_id = p.id
                         WHERE ii.inventory_id = ?");
        $this->db->bind(1, $inventoryId);
        return $this->db->resultSet();
    }

    /**
     * Create physical inventory
     */
    public function createInventory($data, $items) {
        // Generate inventory number
        $inventoryNumber = $this->generateInventoryNumber();

        $this->db->query("INSERT INTO physical_inventories
                         (inventory_number, location_id, inventory_date,
                          notes, status, created_by)
                         VALUES (?, ?, ?, ?, 'in_progress', ?)");

        $this->db->bind(1, $inventoryNumber);
        $this->db->bind(2, $data['location_id']);
        $this->db->bind(3, $data['inventory_date']);
        $this->db->bind(4, $data['notes'] ?? null);
        $this->db->bind(5, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            $inventoryId = $this->db->lastInsertId();

            // Add items
            foreach ($items as $item) {
                $this->addInventoryItem($inventoryId, $item);
            }

            return $inventoryId;
        }

        return false;
    }

    /**
     * Add inventory item
     */
    private function addInventoryItem($inventoryId, $item) {
        $variance = floatval($item['counted_quantity']) - floatval($item['system_quantity']);

        $this->db->query("INSERT INTO inventory_items
                         (inventory_id, part_id, system_quantity, counted_quantity,
                          variance, unit_cost, notes)
                         VALUES (?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $inventoryId);
        $this->db->bind(2, $item['part_id']);
        $this->db->bind(3, $item['system_quantity']);
        $this->db->bind(4, $item['counted_quantity']);
        $this->db->bind(5, $variance);
        $this->db->bind(6, $item['unit_cost'] ?? 0);
        $this->db->bind(7, $item['notes'] ?? null);

        return $this->db->execute();
    }

    /**
     * Validate inventory and apply adjustments
     */
    public function validateInventory($id) {
        $inventory = $this->getInventoryById($id);

        if (!$inventory || $inventory['status'] != 'in_progress') {
            return false;
        }

        // Update inventory status
        $this->db->query("UPDATE physical_inventories
                         SET status = 'validated',
                             validated_by = ?,
                             validated_at = NOW()
                         WHERE id = ?");
        $this->db->bind(1, $_SESSION['user_id'] ?? null);
        $this->db->bind(2, $id);

        if (!$this->db->execute()) {
            return false;
        }

        // Apply stock adjustments
        foreach ($inventory['items'] as $item) {
            if ($item['variance'] != 0) {
                // Update part stock to counted quantity
                $this->setPartStock($item['part_id'], $item['counted_quantity'], $inventory['location_id']);
            }
        }

        return true;
    }

    /**
     * Generate inventory number
     */
    private function generateInventoryNumber() {
        $year = date('Y');
        $prefix = 'INV-' . $year . '-';

        $this->db->query("SELECT inventory_number FROM physical_inventories
                         WHERE inventory_number LIKE ?
                         ORDER BY inventory_number DESC LIMIT 1");
        $this->db->bind(1, $prefix . '%');
        $result = $this->db->single();

        if ($result) {
            $lastNumber = intval(substr($result['inventory_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Get stock statistics
     */
    public function getStockStats() {
        $stats = [];

        // Total locations
        $this->db->query("SELECT COUNT(*) as count FROM stock_locations WHERE is_active = 1");
        $result = $this->db->single();
        $stats['total_locations'] = $result['count'];

        // Pending documents
        $this->db->query("SELECT COUNT(*) as count FROM stock_documents WHERE status = 'draft'");
        $result = $this->db->single();
        $stats['pending_documents'] = $result['count'];

        // In-progress inventories
        $this->db->query("SELECT COUNT(*) as count FROM physical_inventories WHERE status = 'in_progress'");
        $result = $this->db->single();
        $stats['active_inventories'] = $result['count'];

        // Total stock value
        $this->db->query("SELECT SUM(quantity * price) as total FROM parts");
        $result = $this->db->single();
        $stats['total_stock_value'] = $result['total'] ?? 0;

        return $stats;
    }
}
