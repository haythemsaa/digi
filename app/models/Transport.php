<?php
/**
 * Transport Model - Multi-tenant enabled
 * Handles transport quotes, orders, and invoices
 */

class Transport extends Database {
    private $companyId;

    public function __construct() {
        parent::__construct();
        $this->companyId = getCurrentCompanyId();

        // Ensure company context exists
        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    /**
     * Get company filter for SQL queries
     */
    private function getCompanyFilter($tableAlias = '') {
        if (isSuperAdmin()) {
            return '1=1'; // No filter for super admins
        }
        $prefix = $tableAlias ? "{$tableAlias}." : '';
        return "{$prefix}company_id = :company_id";
    }

    /**
     * Bind company ID to query
     */
    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->bind(':company_id', $this->companyId);
        }
    }

    // ========== CLIENTS ==========
    public function getAllClients() {
        $companyFilter = $this->getCompanyFilter('');
        $this->query("SELECT * FROM clients WHERE {$companyFilter} ORDER BY company_name, first_name, last_name");
        $this->bindCompanyId();
        return $this->fetchAll();
    }

    public function getClientById($id) {
        $companyFilter = $this->getCompanyFilter('');
        $this->query("SELECT * FROM clients WHERE id = :id AND {$companyFilter}");
        $this->bind(':id', $id);
        $this->bindCompanyId();
        return $this->fetch();
    }

    public function addClient($data) {
        $this->query('INSERT INTO clients (company_id, client_type, company_name, first_name, last_name,
            email, phone, mobile, tax_id, address, city, postal_code, country,
            payment_terms, credit_limit, status, notes, created_by)
            VALUES (:company_id, :client_type, :company_name, :first_name, :last_name,
            :email, :phone, :mobile, :tax_id, :address, :city, :postal_code, :country,
            :payment_terms, :credit_limit, :status, :notes, :created_by)');

        $this->bind(':company_id', $this->companyId);
        $this->bind(':client_type', $data['client_type']);
        $this->bind(':company_name', $data['company_name'] ?? null);
        $this->bind(':first_name', $data['first_name'] ?? null);
        $this->bind(':last_name', $data['last_name'] ?? null);
        $this->bind(':email', $data['email'] ?? null);
        $this->bind(':phone', $data['phone'] ?? null);
        $this->bind(':mobile', $data['mobile'] ?? null);
        $this->bind(':tax_id', $data['tax_id'] ?? null);
        $this->bind(':address', $data['address'] ?? null);
        $this->bind(':city', $data['city'] ?? null);
        $this->bind(':postal_code', $data['postal_code'] ?? null);
        $this->bind(':country', $data['country'] ?? null);
        $this->bind(':payment_terms', $data['payment_terms'] ?? 30);
        $this->bind(':credit_limit', $data['credit_limit'] ?? null);
        $this->bind(':status', $data['status'] ?? 'active');
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    // ========== QUOTES ==========
    public function getAllQuotes() {
        $companyFilter = $this->getCompanyFilter('q');
        $this->query("SELECT q.*, c.company_name, c.first_name, c.last_name
            FROM transport_quotes q
            LEFT JOIN clients c ON q.client_id = c.id
            WHERE {$companyFilter}
            ORDER BY q.created_at DESC");
        $this->bindCompanyId();
        return $this->fetchAll();
    }

    public function getQuoteById($id) {
        $companyFilter = $this->getCompanyFilter('q');
        $this->query("SELECT q.*, c.*
            FROM transport_quotes q
            LEFT JOIN clients c ON q.client_id = c.id
            WHERE q.id = :id AND {$companyFilter}");
        $this->bind(':id', $id);
        $this->bindCompanyId();
        return $this->fetch();
    }

    public function addQuote($data) {
        $this->query('INSERT INTO transport_quotes (
            company_id, quote_number, client_id, pickup_address, pickup_city, pickup_date,
            delivery_address, delivery_city, delivery_date, distance,
            cargo_type, cargo_weight, cargo_volume, vehicle_type_required,
            price, tax_rate, tax_amount, total_amount, status, valid_until, notes, created_by
        ) VALUES (
            :company_id, :quote_number, :client_id, :pickup_address, :pickup_city, :pickup_date,
            :delivery_address, :delivery_city, :delivery_date, :distance,
            :cargo_type, :cargo_weight, :cargo_volume, :vehicle_type_required,
            :price, :tax_rate, :tax_amount, :total_amount, :status, :valid_until, :notes, :created_by
        )');

        $taxAmount = ($data['price'] * ($data['tax_rate'] ?? 0)) / 100;
        $totalAmount = $data['price'] + $taxAmount;

        $this->bind(':company_id', $this->companyId);
        $this->bind(':quote_number', $data['quote_number']);
        $this->bind(':client_id', $data['client_id']);
        $this->bind(':pickup_address', $data['pickup_address']);
        $this->bind(':pickup_city', $data['pickup_city']);
        $this->bind(':pickup_date', $data['pickup_date']);
        $this->bind(':delivery_address', $data['delivery_address']);
        $this->bind(':delivery_city', $data['delivery_city']);
        $this->bind(':delivery_date', $data['delivery_date'] ?? null);
        $this->bind(':distance', $data['distance'] ?? null);
        $this->bind(':cargo_type', $data['cargo_type'] ?? null);
        $this->bind(':cargo_weight', $data['cargo_weight'] ?? null);
        $this->bind(':cargo_volume', $data['cargo_volume'] ?? null);
        $this->bind(':vehicle_type_required', $data['vehicle_type_required'] ?? null);
        $this->bind(':price', $data['price']);
        $this->bind(':tax_rate', $data['tax_rate'] ?? 0);
        $this->bind(':tax_amount', $taxAmount);
        $this->bind(':total_amount', $totalAmount);
        $this->bind(':status', $data['status'] ?? 'draft');
        $this->bind(':valid_until', $data['valid_until'] ?? null);
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    // ========== ORDERS ==========
    public function getAllOrders() {
        $companyFilter = $this->getCompanyFilter('o');
        $this->query("SELECT o.*, c.company_name, c.first_name, c.last_name,
            v.registration_number, d.first_name as driver_first, d.last_name as driver_last
            FROM transport_orders o
            LEFT JOIN clients c ON o.client_id = c.id
            LEFT JOIN vehicles v ON o.vehicle_id = v.id
            LEFT JOIN users d ON o.driver_id = d.id
            WHERE {$companyFilter}
            ORDER BY o.created_at DESC");
        $this->bindCompanyId();
        return $this->fetchAll();
    }

    public function getOrderById($id) {
        $companyFilter = $this->getCompanyFilter('o');
        $this->query("SELECT o.*, c.*
            FROM transport_orders o
            LEFT JOIN clients c ON o.client_id = c.id
            WHERE o.id = :id AND {$companyFilter}");
        $this->bind(':id', $id);
        $this->bindCompanyId();
        return $this->fetch();
    }

    public function addOrder($data) {
        $this->query('INSERT INTO transport_orders (
            company_id, order_number, quote_id, client_id, vehicle_id, driver_id,
            pickup_address, pickup_city, pickup_date, pickup_contact, pickup_phone,
            delivery_address, delivery_city, delivery_date, delivery_contact, delivery_phone,
            distance, cargo_type, cargo_description, cargo_weight, cargo_volume,
            price, tax_rate, tax_amount, total_amount, status, priority, special_instructions, created_by
        ) VALUES (
            :company_id, :order_number, :quote_id, :client_id, :vehicle_id, :driver_id,
            :pickup_address, :pickup_city, :pickup_date, :pickup_contact, :pickup_phone,
            :delivery_address, :delivery_city, :delivery_date, :delivery_contact, :delivery_phone,
            :distance, :cargo_type, :cargo_description, :cargo_weight, :cargo_volume,
            :price, :tax_rate, :tax_amount, :total_amount, :status, :priority, :special_instructions, :created_by
        )');

        $taxAmount = ($data['price'] * ($data['tax_rate'] ?? 0)) / 100;
        $totalAmount = $data['price'] + $taxAmount;

        $this->bind(':company_id', $this->companyId);
        $this->bind(':order_number', $data['order_number']);
        $this->bind(':quote_id', $data['quote_id'] ?? null);
        $this->bind(':client_id', $data['client_id']);
        $this->bind(':vehicle_id', $data['vehicle_id'] ?? null);
        $this->bind(':driver_id', $data['driver_id'] ?? null);
        $this->bind(':pickup_address', $data['pickup_address']);
        $this->bind(':pickup_city', $data['pickup_city']);
        $this->bind(':pickup_date', $data['pickup_date']);
        $this->bind(':pickup_contact', $data['pickup_contact'] ?? null);
        $this->bind(':pickup_phone', $data['pickup_phone'] ?? null);
        $this->bind(':delivery_address', $data['delivery_address']);
        $this->bind(':delivery_city', $data['delivery_city']);
        $this->bind(':delivery_date', $data['delivery_date'] ?? null);
        $this->bind(':delivery_contact', $data['delivery_contact'] ?? null);
        $this->bind(':delivery_phone', $data['delivery_phone'] ?? null);
        $this->bind(':distance', $data['distance'] ?? null);
        $this->bind(':cargo_type', $data['cargo_type'] ?? null);
        $this->bind(':cargo_description', $data['cargo_description'] ?? null);
        $this->bind(':cargo_weight', $data['cargo_weight'] ?? null);
        $this->bind(':cargo_volume', $data['cargo_volume'] ?? null);
        $this->bind(':price', $data['price']);
        $this->bind(':tax_rate', $data['tax_rate'] ?? 0);
        $this->bind(':tax_amount', $taxAmount);
        $this->bind(':total_amount', $totalAmount);
        $this->bind(':status', $data['status'] ?? 'pending');
        $this->bind(':priority', $data['priority'] ?? 'normal');
        $this->bind(':special_instructions', $data['special_instructions'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function updateOrderStatus($id, $status) {
        $companyFilter = $this->getCompanyFilter('');
        $this->query("UPDATE transport_orders SET status = :status WHERE id = :id AND {$companyFilter}");
        $this->bind(':id', $id);
        $this->bind(':status', $status);
        $this->bindCompanyId();
        return $this->execute();
    }

    // ========== INVOICES ==========
    public function getAllInvoices() {
        $companyFilter = $this->getCompanyFilter('i');
        $this->query("SELECT i.*, c.company_name, c.first_name, c.last_name
            FROM invoices i
            LEFT JOIN clients c ON i.client_id = c.id
            WHERE {$companyFilter}
            ORDER BY i.created_at DESC");
        $this->bindCompanyId();
        return $this->fetchAll();
    }

    public function getInvoiceById($id) {
        $companyFilter = $this->getCompanyFilter('i');
        $this->query("SELECT i.*, c.*, o.order_number
            FROM invoices i
            LEFT JOIN clients c ON i.client_id = c.id
            LEFT JOIN transport_orders o ON i.transport_order_id = o.id
            WHERE i.id = :id AND {$companyFilter}");
        $this->bind(':id', $id);
        $this->bindCompanyId();
        return $this->fetch();
    }

    public function addInvoice($data) {
        $this->query('INSERT INTO invoices (
            company_id, invoice_number, transport_order_id, client_id, invoice_date, due_date,
            subtotal, tax_rate, tax_amount, discount, total_amount, paid_amount,
            balance, status, payment_method, notes, created_by
        ) VALUES (
            :company_id, :invoice_number, :transport_order_id, :client_id, :invoice_date, :due_date,
            :subtotal, :tax_rate, :tax_amount, :discount, :total_amount, :paid_amount,
            :balance, :status, :payment_method, :notes, :created_by
        )');

        $taxAmount = ($data['subtotal'] * ($data['tax_rate'] ?? 0)) / 100;
        $totalAmount = $data['subtotal'] + $taxAmount - ($data['discount'] ?? 0);
        $balance = $totalAmount - ($data['paid_amount'] ?? 0);

        $this->bind(':company_id', $this->companyId);
        $this->bind(':invoice_number', $data['invoice_number']);
        $this->bind(':transport_order_id', $data['transport_order_id'] ?? null);
        $this->bind(':client_id', $data['client_id']);
        $this->bind(':invoice_date', $data['invoice_date']);
        $this->bind(':due_date', $data['due_date']);
        $this->bind(':subtotal', $data['subtotal']);
        $this->bind(':tax_rate', $data['tax_rate'] ?? 0);
        $this->bind(':tax_amount', $taxAmount);
        $this->bind(':discount', $data['discount'] ?? 0);
        $this->bind(':total_amount', $totalAmount);
        $this->bind(':paid_amount', $data['paid_amount'] ?? 0);
        $this->bind(':balance', $balance);
        $this->bind(':status', $balance == 0 ? 'paid' : ($data['paid_amount'] > 0 ? 'partially_paid' : 'draft'));
        $this->bind(':payment_method', $data['payment_method'] ?? null);
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function generateQuoteNumber() {
        $companyFilter = $this->getCompanyFilter('');
        $prefix = 'QT-' . date('Y') . '-';
        $this->query("SELECT COUNT(*) as count FROM transport_quotes WHERE quote_number LIKE :prefix AND {$companyFilter}");
        $this->bind(':prefix', $prefix . '%');
        $this->bindCompanyId();
        $result = $this->fetch();
        return $prefix . str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
    }

    public function generateOrderNumber() {
        $companyFilter = $this->getCompanyFilter('');
        $prefix = 'TO-' . date('Y') . '-';
        $this->query("SELECT COUNT(*) as count FROM transport_orders WHERE order_number LIKE :prefix AND {$companyFilter}");
        $this->bind(':prefix', $prefix . '%');
        $this->bindCompanyId();
        $result = $this->fetch();
        return $prefix . str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
    }

    public function generateInvoiceNumber() {
        $companyFilter = $this->getCompanyFilter('');
        $prefix = 'INV-' . date('Y') . '-';
        $this->query("SELECT COUNT(*) as count FROM invoices WHERE invoice_number LIKE :prefix AND {$companyFilter}");
        $this->bind(':prefix', $prefix . '%');
        $this->bindCompanyId();
        $result = $this->fetch();
        return $prefix . str_pad($result['count'] + 1, 4, '0', STR_PAD_LEFT);
    }
}
