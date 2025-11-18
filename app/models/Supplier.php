<?php
/**
 * Supplier Model
 */

class Supplier extends Database {

    public function __construct() {
        parent::__construct();
    }

    public function getAllSuppliers() {
        $this->query('SELECT * FROM suppliers ORDER BY company_name');
        return $this->fetchAll();
    }

    public function getSupplierById($id) {
        $this->query('SELECT * FROM suppliers WHERE id = :id');
        $this->bind(':id', $id);
        return $this->fetch();
    }

    public function addSupplier($data) {
        $this->query('INSERT INTO suppliers (
            company_name, contact_person, email, phone, mobile, tax_id,
            address, city, postal_code, country, payment_terms, category, status, notes, created_by
        ) VALUES (
            :company_name, :contact_person, :email, :phone, :mobile, :tax_id,
            :address, :city, :postal_code, :country, :payment_terms, :category, :status, :notes, :created_by
        )');

        $this->bind(':company_name', $data['company_name']);
        $this->bind(':contact_person', $data['contact_person'] ?? null);
        $this->bind(':email', $data['email'] ?? null);
        $this->bind(':phone', $data['phone'] ?? null);
        $this->bind(':mobile', $data['mobile'] ?? null);
        $this->bind(':tax_id', $data['tax_id'] ?? null);
        $this->bind(':address', $data['address'] ?? null);
        $this->bind(':city', $data['city'] ?? null);
        $this->bind(':postal_code', $data['postal_code'] ?? null);
        $this->bind(':country', $data['country'] ?? null);
        $this->bind(':payment_terms', $data['payment_terms'] ?? 30);
        $this->bind(':category', $data['category']);
        $this->bind(':status', $data['status'] ?? 'active');
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function getPurchaseOrders() {
        $this->query('SELECT po.*, s.company_name
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            ORDER BY po.created_at DESC');
        return $this->fetchAll();
    }

    public function addPurchaseOrder($data) {
        $this->query('INSERT INTO purchase_orders (
            po_number, supplier_id, order_date, expected_delivery_date,
            subtotal, tax_rate, tax_amount, total_amount, status, notes, created_by
        ) VALUES (
            :po_number, :supplier_id, :order_date, :expected_delivery_date,
            :subtotal, :tax_rate, :tax_amount, :total_amount, :status, :notes, :created_by
        )');

        $taxAmount = ($data['subtotal'] * ($data['tax_rate'] ?? 0)) / 100;
        $totalAmount = $data['subtotal'] + $taxAmount;

        $this->bind(':po_number', $data['po_number']);
        $this->bind(':supplier_id', $data['supplier_id']);
        $this->bind(':order_date', $data['order_date']);
        $this->bind(':expected_delivery_date', $data['expected_delivery_date'] ?? null);
        $this->bind(':subtotal', $data['subtotal']);
        $this->bind(':tax_rate', $data['tax_rate'] ?? 0);
        $this->bind(':tax_amount', $taxAmount);
        $this->bind(':total_amount', $totalAmount);
        $this->bind(':status', $data['status'] ?? 'draft');
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function generatePONumber() {
        $prefix = 'PO-' . date('Y') . '-';
        $this->query('SELECT COUNT(*) as count FROM purchase_orders WHERE po_number LIKE :prefix');
        $this->bind(':prefix', $prefix . '%');
        $result = $this->fetch();
        return $prefix . str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getPurchaseOrderById($id) {
        $this->query('SELECT po.*, s.company_name, s.email, s.phone, s.address, s.city
            FROM purchase_orders po
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            WHERE po.id = :id');
        $this->bind(':id', $id);
        return $this->fetch();
    }
}
