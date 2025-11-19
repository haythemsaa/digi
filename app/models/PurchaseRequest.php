<?php
/**
 * Purchase Request Model - Multi-tenant enabled
 * Manages purchase requests and delivery notes workflow
 */

class PurchaseRequest extends Model {
    private $companyId;

    public function __construct() {
        parent::__construct();
        $this->companyId = getCurrentCompanyId();

        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    private function getCompanyFilter($tableAlias = '') {
        if (isSuperAdmin()) {
            return '1=1';
        }
        $prefix = $tableAlias ? "{$tableAlias}." : '';
        return "{$prefix}company_id = :company_id";
    }

    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    public function getAllRequests($filters = []) {
        $companyFilter = $this->getCompanyFilter('pr');
        $sql = "SELECT pr.*,
                       CONCAT(u1.first_name, ' ', u1.last_name) as requested_by_name,
                       CONCAT(u2.first_name, ' ', u2.last_name) as approved_by_name,
                       d.name as department_name
                FROM purchase_requests pr
                LEFT JOIN users u1 ON pr.requested_by = u1.id
                LEFT JOIN users u2 ON pr.approved_by = u2.id
                LEFT JOIN departments d ON pr.department_id = d.id
                WHERE {$companyFilter}";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND pr.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND pr.department_id = ?";
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND pr.priority = ?";
            $params[] = $filters['priority'];
        }

        $sql .= " ORDER BY pr.created_at DESC";

        $this->db->query($sql);
        $this->bindCompanyId();
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    public function getRequestById($id) {
        $companyFilter = $this->getCompanyFilter('pr');
        $this->db->query("SELECT pr.*,
                                 CONCAT(u1.first_name, ' ', u1.last_name) as requested_by_name,
                                 CONCAT(u2.first_name, ' ', u2.last_name) as approved_by_name,
                                 d.name as department_name
                          FROM purchase_requests pr
                          LEFT JOIN users u1 ON pr.requested_by = u1.id
                          LEFT JOIN users u2 ON pr.approved_by = u2.id
                          LEFT JOIN departments d ON pr.department_id = d.id
                          WHERE pr.id = ? AND {$companyFilter}");
        $this->db->bind(1, $id);
        $this->bindCompanyId();
        $request = $this->db->single();

        if ($request) {
            $request['items'] = $this->getRequestItems($id);
        }

        return $request;
    }

    public function getRequestItems($requestId) {
        $companyFilter = $this->getCompanyFilter('pri');
        $this->db->query("SELECT pri.*, p.name as part_name, p.reference as part_reference
                         FROM purchase_request_items pri
                         LEFT JOIN parts p ON pri.part_id = p.id
                         WHERE pri.request_id = ? AND {$companyFilter}");
        $this->db->bind(1, $requestId);
        $this->bindCompanyId();
        return $this->db->resultSet();
    }

    public function createRequest($data, $items) {
        $requestNumber = $this->generateRequestNumber();

        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += floatval($item['quantity']) * floatval($item['estimated_price']);
        }

        $this->db->query("INSERT INTO purchase_requests
                         (company_id, request_number, department_id, title, description,
                          justification, needed_by, priority, total_amount,
                          status, requested_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $requestNumber);
        $this->db->bind(3, $data['department_id']);
        $this->db->bind(4, $data['title']);
        $this->db->bind(5, $data['description'] ?? null);
        $this->db->bind(6, $data['justification'] ?? null);
        $this->db->bind(7, $data['needed_by'] ?? null);
        $this->db->bind(8, $data['priority'] ?? 'normal');
        $this->db->bind(9, $totalAmount);
        $this->db->bind(10, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            $requestId = $this->db->lastInsertId();

            foreach ($items as $item) {
                $this->addRequestItem($requestId, $item);
            }

            return $requestId;
        }

        return false;
    }

    private function addRequestItem($requestId, $item) {
        $this->db->query("INSERT INTO purchase_request_items
                         (company_id, request_id, part_id, description, quantity, unit,
                          estimated_price, notes)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $requestId);
        $this->db->bind(3, $item['part_id'] ?? null);
        $this->db->bind(4, $item['description']);
        $this->db->bind(5, $item['quantity']);
        $this->db->bind(6, $item['unit'] ?? 'pcs');
        $this->db->bind(7, $item['estimated_price']);
        $this->db->bind(8, $item['notes'] ?? null);

        return $this->db->execute();
    }

    public function updateRequestStatus($id, $status, $rejectionReason = null) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE purchase_requests
                         SET status = ?,
                             approved_by = ?,
                             approved_at = ?,
                             rejection_reason = ?
                         WHERE id = ? AND {$companyFilter}");

        $this->db->bind(1, $status);
        $this->db->bind(2, ($status == 'approved' || $status == 'rejected') ? $_SESSION['user_id'] : null);
        $this->db->bind(3, ($status == 'approved' || $status == 'rejected') ? date('Y-m-d H:i:s') : null);
        $this->db->bind(4, $rejectionReason);
        $this->db->bind(5, $id);
        $this->bindCompanyId();

        return $this->db->execute();
    }

    private function generateRequestNumber() {
        $companyFilter = $this->getCompanyFilter('');
        $year = date('Y');
        $prefix = 'PR-' . $year . '-';

        $this->db->query("SELECT request_number FROM purchase_requests
                         WHERE request_number LIKE ? AND {$companyFilter}
                         ORDER BY request_number DESC LIMIT 1");
        $this->db->bind(1, $prefix . '%');
        $this->bindCompanyId();
        $result = $this->db->single();

        if ($result) {
            $lastNumber = intval(substr($result['request_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    // ==================== DELIVERY NOTES ====================

    public function getAllDeliveryNotes($filters = []) {
        $companyFilter = $this->getCompanyFilter('dn');
        $sql = "SELECT dn.*,
                       s.name as supplier_name,
                       po.order_number as po_number,
                       CONCAT(u.first_name, ' ', u.last_name) as received_by_name
                FROM delivery_notes dn
                LEFT JOIN suppliers s ON dn.supplier_id = s.id
                LEFT JOIN purchase_orders po ON dn.purchase_order_id = po.id
                LEFT JOIN users u ON dn.received_by = u.id
                WHERE {$companyFilter}";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND dn.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND dn.supplier_id = ?";
            $params[] = $filters['supplier_id'];
        }

        if (!empty($filters['purchase_order_id'])) {
            $sql .= " AND dn.purchase_order_id = ?";
            $params[] = $filters['purchase_order_id'];
        }

        $sql .= " ORDER BY dn.delivery_date DESC";

        $this->db->query($sql);
        $this->bindCompanyId();
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    public function getDeliveryNoteById($id) {
        $companyFilter = $this->getCompanyFilter('dn');
        $this->db->query("SELECT dn.*,
                                 s.name as supplier_name, s.phone as supplier_phone,
                                 po.order_number as po_number,
                                 CONCAT(u.first_name, ' ', u.last_name) as received_by_name
                          FROM delivery_notes dn
                          LEFT JOIN suppliers s ON dn.supplier_id = s.id
                          LEFT JOIN purchase_orders po ON dn.purchase_order_id = po.id
                          LEFT JOIN users u ON dn.received_by = u.id
                          WHERE dn.id = ? AND {$companyFilter}");
        $this->db->bind(1, $id);
        $this->bindCompanyId();
        $note = $this->db->single();

        if ($note) {
            $note['items'] = $this->getDeliveryNoteItems($id);
        }

        return $note;
    }

    public function getDeliveryNoteItems($noteId) {
        $companyFilter = $this->getCompanyFilter('dni');
        $this->db->query("SELECT dni.*, p.name as part_name, p.reference as part_reference
                         FROM delivery_note_items dni
                         LEFT JOIN parts p ON dni.part_id = p.id
                         WHERE dni.delivery_note_id = ? AND {$companyFilter}");
        $this->db->bind(1, $noteId);
        $this->bindCompanyId();
        return $this->db->resultSet();
    }

    public function createDeliveryNote($data, $items) {
        $noteNumber = $this->generateDeliveryNoteNumber();

        $this->db->query("INSERT INTO delivery_notes
                         (company_id, note_number, purchase_order_id, supplier_id,
                          delivery_date, carrier, tracking_number,
                          notes, status, received_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $noteNumber);
        $this->db->bind(3, $data['purchase_order_id'] ?? null);
        $this->db->bind(4, $data['supplier_id']);
        $this->db->bind(5, $data['delivery_date']);
        $this->db->bind(6, $data['carrier'] ?? null);
        $this->db->bind(7, $data['tracking_number'] ?? null);
        $this->db->bind(8, $data['notes'] ?? null);
        $this->db->bind(9, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            $noteId = $this->db->lastInsertId();

            foreach ($items as $item) {
                $this->addDeliveryNoteItem($noteId, $item);
            }

            return $noteId;
        }

        return false;
    }

    private function addDeliveryNoteItem($noteId, $item) {
        $this->db->query("INSERT INTO delivery_note_items
                         (company_id, delivery_note_id, part_id, description,
                          quantity_ordered, quantity_received,
                          unit_price, condition_status, notes)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $this->companyId);
        $this->db->bind(2, $noteId);
        $this->db->bind(3, $item['part_id'] ?? null);
        $this->db->bind(4, $item['description']);
        $this->db->bind(5, $item['quantity_ordered']);
        $this->db->bind(6, $item['quantity_received']);
        $this->db->bind(7, $item['unit_price'] ?? 0);
        $this->db->bind(8, $item['condition_status'] ?? 'good');
        $this->db->bind(9, $item['notes'] ?? null);

        return $this->db->execute();
    }

    public function updateDeliveryNoteStatus($id, $status) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE delivery_notes SET status = ? WHERE id = ? AND {$companyFilter}");
        $this->db->bind(1, $status);
        $this->db->bind(2, $id);
        $this->bindCompanyId();

        if ($this->db->execute()) {
            if ($status == 'validated') {
                $this->updateStockFromDeliveryNote($id);
            }
            return true;
        }

        return false;
    }

    private function updateStockFromDeliveryNote($noteId) {
        $items = $this->getDeliveryNoteItems($noteId);

        foreach ($items as $item) {
            if ($item['part_id'] && $item['quantity_received'] > 0) {
                $companyFilter = $this->getCompanyFilter('');
                $this->db->query("UPDATE parts
                                 SET quantity = quantity + ?
                                 WHERE id = ? AND {$companyFilter}");
                $this->db->bind(1, $item['quantity_received']);
                $this->db->bind(2, $item['part_id']);
                $this->bindCompanyId();
                $this->db->execute();
            }
        }
    }

    private function generateDeliveryNoteNumber() {
        $companyFilter = $this->getCompanyFilter('');
        $year = date('Y');
        $prefix = 'DN-' . $year . '-';

        $this->db->query("SELECT note_number FROM delivery_notes
                         WHERE note_number LIKE ? AND {$companyFilter}
                         ORDER BY note_number DESC LIMIT 1");
        $this->db->bind(1, $prefix . '%');
        $this->bindCompanyId();
        $result = $this->db->single();

        if ($result) {
            $lastNumber = intval(substr($result['note_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    public function getRequestStats() {
        $companyFilter = $this->getCompanyFilter('');
        $stats = [];

        $this->db->query("SELECT COUNT(*) as count FROM purchase_requests WHERE status = 'pending' AND {$companyFilter}");
        $this->bindCompanyId();
        $result = $this->db->single();
        $stats['pending_requests'] = $result['count'];

        $this->db->query("SELECT COUNT(*) as count FROM purchase_requests WHERE status = 'approved' AND {$companyFilter}");
        $this->bindCompanyId();
        $result = $this->db->single();
        $stats['approved_requests'] = $result['count'];

        $this->db->query("SELECT SUM(total_amount) as total FROM purchase_requests WHERE status = 'pending' AND {$companyFilter}");
        $this->bindCompanyId();
        $result = $this->db->single();
        $stats['pending_value'] = $result['total'] ?? 0;

        return $stats;
    }
}
