<?php
/**
 * Supplier Model - Multi-tenant enabled
 */

class Supplier {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();

        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    private function getCompanyFilter($tableAlias = 's') {
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

    // ========== SUPPLIERS ==========

    public function getAllSuppliers() {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("SELECT * FROM suppliers s
            WHERE {$filter}
            ORDER BY s.company_name");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getSupplierById($id) {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("SELECT * FROM suppliers s WHERE s.id = :id AND {$filter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();

        return $this->db->fetch();
    }

    public function getSuppliersByCategory($category) {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("SELECT * FROM suppliers s
            WHERE s.category = :category AND {$filter}
            ORDER BY s.company_name");

        $this->db->bind(':category', $category);
        $this->bindCompanyId();

        return $this->db->fetchAll();
    }

    public function searchSuppliers($keyword) {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("SELECT * FROM suppliers s
            WHERE {$filter}
            AND (s.company_name LIKE :keyword
               OR s.contact_person LIKE :keyword
               OR s.email LIKE :keyword)
            ORDER BY s.company_name");

        $this->bindCompanyId();
        $this->db->bind(':keyword', '%' . $keyword . '%');

        return $this->db->fetchAll();
    }

    public function addSupplier($data) {
        $this->db->query('INSERT INTO suppliers (
            company_id, company_name, contact_person, email, phone, mobile, tax_id,
            address, city, postal_code, country, payment_terms, category, status, notes, created_by
        ) VALUES (
            :company_id, :company_name, :contact_person, :email, :phone, :mobile, :tax_id,
            :address, :city, :postal_code, :country, :payment_terms, :category, :status, :notes, :created_by
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':contact_person', $data['contact_person'] ?? null);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':mobile', $data['mobile'] ?? null);
        $this->db->bind(':tax_id', $data['tax_id'] ?? null);
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':city', $data['city'] ?? null);
        $this->db->bind(':postal_code', $data['postal_code'] ?? null);
        $this->db->bind(':country', $data['country'] ?? null);
        $this->db->bind(':payment_terms', $data['payment_terms'] ?? 30);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':created_by', $_SESSION['user_id']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateSupplier($id, $data) {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("UPDATE suppliers s SET
            s.company_name = :company_name,
            s.contact_person = :contact_person,
            s.email = :email,
            s.phone = :phone,
            s.mobile = :mobile,
            s.tax_id = :tax_id,
            s.address = :address,
            s.city = :city,
            s.postal_code = :postal_code,
            s.country = :country,
            s.payment_terms = :payment_terms,
            s.category = :category,
            s.status = :status,
            s.notes = :notes
            WHERE s.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':contact_person', $data['contact_person'] ?? null);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':mobile', $data['mobile'] ?? null);
        $this->db->bind(':tax_id', $data['tax_id'] ?? null);
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':city', $data['city'] ?? null);
        $this->db->bind(':postal_code', $data['postal_code'] ?? null);
        $this->db->bind(':country', $data['country'] ?? null);
        $this->db->bind(':payment_terms', $data['payment_terms'] ?? 30);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':notes', $data['notes'] ?? null);

        return $this->db->execute();
    }

    // ========== PURCHASE ORDERS ==========

    public function getPurchaseOrders() {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("SELECT po.*, s.company_name
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            WHERE {$filter}
            ORDER BY po.created_at DESC");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    public function getPurchaseOrderById($id) {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("SELECT po.*, s.company_name, s.email, s.phone, s.address, s.city
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            WHERE po.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();

        return $this->db->fetch();
    }

    public function addPurchaseOrder($data) {
        // Verify supplier belongs to company
        $supplier = $this->getSupplierById($data['supplier_id']);
        if (!$supplier) {
            return false;
        }

        $taxAmount = ($data['subtotal'] * ($data['tax_rate'] ?? 0)) / 100;
        $totalAmount = $data['subtotal'] + $taxAmount;

        $this->db->query('INSERT INTO purchase_orders (
            po_number, supplier_id, order_date, expected_delivery_date,
            subtotal, tax_rate, tax_amount, total_amount, status, notes, created_by
        ) VALUES (
            :po_number, :supplier_id, :order_date, :expected_delivery_date,
            :subtotal, :tax_rate, :tax_amount, :total_amount, :status, :notes, :created_by
        )');

        $this->db->bind(':po_number', $data['po_number']);
        $this->db->bind(':supplier_id', $data['supplier_id']);
        $this->db->bind(':order_date', $data['order_date']);
        $this->db->bind(':expected_delivery_date', $data['expected_delivery_date'] ?? null);
        $this->db->bind(':subtotal', $data['subtotal']);
        $this->db->bind(':tax_rate', $data['tax_rate'] ?? 0);
        $this->db->bind(':tax_amount', $taxAmount);
        $this->db->bind(':total_amount', $totalAmount);
        $this->db->bind(':status', $data['status'] ?? 'draft');
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':created_by', $_SESSION['user_id']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updatePurchaseOrder($id, $data) {
        $filter = $this->getCompanyFilter('s');

        $taxAmount = ($data['subtotal'] * ($data['tax_rate'] ?? 0)) / 100;
        $totalAmount = $data['subtotal'] + $taxAmount;

        $this->db->query("UPDATE purchase_orders po
            INNER JOIN suppliers s ON po.supplier_id = s.id
            SET
            po.order_date = :order_date,
            po.expected_delivery_date = :expected_delivery_date,
            po.subtotal = :subtotal,
            po.tax_rate = :tax_rate,
            po.tax_amount = :tax_amount,
            po.total_amount = :total_amount,
            po.status = :status,
            po.notes = :notes
            WHERE po.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':order_date', $data['order_date']);
        $this->db->bind(':expected_delivery_date', $data['expected_delivery_date'] ?? null);
        $this->db->bind(':subtotal', $data['subtotal']);
        $this->db->bind(':tax_rate', $data['tax_rate'] ?? 0);
        $this->db->bind(':tax_amount', $taxAmount);
        $this->db->bind(':total_amount', $totalAmount);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':notes', $data['notes'] ?? null);

        return $this->db->execute();
    }

    public function generatePONumber() {
        $filter = $this->getCompanyFilter('s');

        $prefix = 'PO-' . date('Y') . '-';
        $this->db->query("SELECT COUNT(*) as count
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            WHERE po.po_number LIKE :prefix AND {$filter}");

        $this->db->bind(':prefix', $prefix . '%');
        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $prefix . str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getDashboardStats() {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("SELECT
            COUNT(DISTINCT s.id) as total_suppliers,
            SUM(CASE WHEN s.status = 'active' THEN 1 ELSE 0 END) as active_suppliers,
            (SELECT COUNT(*) FROM purchase_orders po2
             LEFT JOIN suppliers s2 ON po2.supplier_id = s2.id
             WHERE {$filter} AND po2.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as pos_last_30_days,
            (SELECT SUM(po3.total_amount) FROM purchase_orders po3
             LEFT JOIN suppliers s3 ON po3.supplier_id = s3.id
             WHERE {$filter} AND po3.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as po_total_last_30_days
            FROM suppliers s
            WHERE {$filter}");

        $this->bindCompanyId();
        return $this->db->fetch();
    }

    public function countSuppliers() {
        $filter = $this->getCompanyFilter('s');

        $this->db->query("SELECT COUNT(*) as total FROM suppliers s WHERE {$filter}");
        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $result['total'];
    }
}
