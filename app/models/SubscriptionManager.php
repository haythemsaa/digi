<?php

class SubscriptionManager {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // ========================================
    // MODULES MANAGEMENT
    // ========================================

    /**
     * Get all subscription modules
     */
    public function getAllModules($activeOnly = false) {
        $sql = "SELECT * FROM subscription_modules";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, module_name ASC";

        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    /**
     * Get module by ID
     */
    public function getModuleById($id) {
        $this->db->query("SELECT * FROM subscription_modules WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get module by code
     */
    public function getModuleByCode($code) {
        $this->db->query("SELECT * FROM subscription_modules WHERE module_code = :code");
        $this->db->bind(':code', $code);
        return $this->db->fetch();
    }

    /**
     * Create a new module
     */
    public function createModule($data) {
        $this->db->query("INSERT INTO subscription_modules
            (module_code, module_name, description, price_monthly, price_yearly, features, icon, color, is_active, sort_order)
            VALUES (:code, :name, :description, :price_monthly, :price_yearly, :features, :icon, :color, :is_active, :sort_order)");

        $this->db->bind(':code', $data['module_code']);
        $this->db->bind(':name', $data['module_name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':price_monthly', $data['price_monthly']);
        $this->db->bind(':price_yearly', $data['price_yearly'] ?? ($data['price_monthly'] * 10));
        $this->db->bind(':features', $data['features'] ?? null);
        $this->db->bind(':icon', $data['icon'] ?? 'fa-star');
        $this->db->bind(':color', $data['color'] ?? '#007bff');
        $this->db->bind(':is_active', $data['is_active'] ?? 1);
        $this->db->bind(':sort_order', $data['sort_order'] ?? 0);

        return $this->db->execute();
    }

    /**
     * Update a module
     */
    public function updateModule($id, $data) {
        $this->db->query("UPDATE subscription_modules SET
            module_name = :name,
            description = :description,
            price_monthly = :price_monthly,
            price_yearly = :price_yearly,
            features = :features,
            icon = :icon,
            color = :color,
            is_active = :is_active,
            sort_order = :sort_order
            WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':name', $data['module_name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':price_monthly', $data['price_monthly']);
        $this->db->bind(':price_yearly', $data['price_yearly']);
        $this->db->bind(':features', $data['features'] ?? null);
        $this->db->bind(':icon', $data['icon'] ?? 'fa-star');
        $this->db->bind(':color', $data['color'] ?? '#007bff');
        $this->db->bind(':is_active', $data['is_active'] ?? 1);
        $this->db->bind(':sort_order', $data['sort_order'] ?? 0);

        return $this->db->execute();
    }

    // ========================================
    // PACKS MANAGEMENT
    // ========================================

    /**
     * Get all subscription packs
     */
    public function getAllPacks($activeOnly = false) {
        $sql = "SELECT * FROM subscription_packs";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, pack_name ASC";

        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    /**
     * Get pack by ID with modules
     */
    public function getPackById($id) {
        $this->db->query("SELECT * FROM subscription_packs WHERE id = :id");
        $this->db->bind(':id', $id);
        $pack = $this->db->fetch();

        if ($pack) {
            $pack['modules'] = $this->getPackModules($id);
        }

        return $pack;
    }

    /**
     * Get pack by code
     */
    public function getPackByCode($code) {
        $this->db->query("SELECT * FROM subscription_packs WHERE pack_code = :code");
        $this->db->bind(':code', $code);
        $pack = $this->db->fetch();

        if ($pack) {
            $pack['modules'] = $this->getPackModules($pack['id']);
        }

        return $pack;
    }

    /**
     * Get modules included in a pack
     */
    public function getPackModules($packId) {
        $this->db->query("SELECT m.*
            FROM subscription_modules m
            INNER JOIN subscription_pack_modules pm ON m.id = pm.module_id
            WHERE pm.pack_id = :pack_id
            ORDER BY m.sort_order ASC");

        $this->db->bind(':pack_id', $packId);
        return $this->db->fetchAll();
    }

    /**
     * Create a new pack
     */
    public function createPack($data, $moduleIds = []) {
        $this->db->query("INSERT INTO subscription_packs
            (pack_code, pack_name, description, price_monthly, price_yearly, discount_percent, is_featured, is_active, sort_order)
            VALUES (:code, :name, :description, :price_monthly, :price_yearly, :discount, :featured, :active, :sort_order)");

        $this->db->bind(':code', $data['pack_code']);
        $this->db->bind(':name', $data['pack_name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':price_monthly', $data['price_monthly']);
        $this->db->bind(':price_yearly', $data['price_yearly'] ?? ($data['price_monthly'] * 10));
        $this->db->bind(':discount', $data['discount_percent'] ?? 0);
        $this->db->bind(':featured', $data['is_featured'] ?? 0);
        $this->db->bind(':active', $data['is_active'] ?? 1);
        $this->db->bind(':sort_order', $data['sort_order'] ?? 0);

        if ($this->db->execute()) {
            $packId = $this->db->lastInsertId();

            // Add modules to pack
            if (!empty($moduleIds)) {
                $this->addModulesToPack($packId, $moduleIds);
            }

            return $packId;
        }

        return false;
    }

    /**
     * Update a pack
     */
    public function updatePack($id, $data, $moduleIds = []) {
        $this->db->query("UPDATE subscription_packs SET
            pack_name = :name,
            description = :description,
            price_monthly = :price_monthly,
            price_yearly = :price_yearly,
            discount_percent = :discount,
            is_featured = :featured,
            is_active = :active,
            sort_order = :sort_order
            WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':name', $data['pack_name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':price_monthly', $data['price_monthly']);
        $this->db->bind(':price_yearly', $data['price_yearly']);
        $this->db->bind(':discount', $data['discount_percent'] ?? 0);
        $this->db->bind(':featured', $data['is_featured'] ?? 0);
        $this->db->bind(':active', $data['is_active'] ?? 1);
        $this->db->bind(':sort_order', $data['sort_order'] ?? 0);

        if ($this->db->execute()) {
            // Update modules
            if (!empty($moduleIds)) {
                // Remove existing modules
                $this->db->query("DELETE FROM subscription_pack_modules WHERE pack_id = :pack_id");
                $this->db->bind(':pack_id', $id);
                $this->db->execute();

                // Add new modules
                $this->addModulesToPack($id, $moduleIds);
            }

            return true;
        }

        return false;
    }

    /**
     * Add modules to a pack
     */
    private function addModulesToPack($packId, $moduleIds) {
        foreach ($moduleIds as $moduleId) {
            $this->db->query("INSERT INTO subscription_pack_modules (pack_id, module_id) VALUES (:pack_id, :module_id)");
            $this->db->bind(':pack_id', $packId);
            $this->db->bind(':module_id', $moduleId);
            $this->db->execute();
        }
    }

    // ========================================
    // COMPANY SUBSCRIPTIONS
    // ========================================

    /**
     * Get all active modules for a company
     */
    public function getCompanyActiveModules($companyId) {
        $this->db->query("SELECT DISTINCT m.*
            FROM subscription_modules m
            LEFT JOIN company_subscriptions cs_module ON cs_module.module_id = m.id AND cs_module.status = 'active'
            LEFT JOIN company_subscriptions cs_pack ON cs_pack.pack_id IS NOT NULL AND cs_pack.status = 'active'
            LEFT JOIN subscription_pack_modules pm ON pm.pack_id = cs_pack.pack_id AND pm.module_id = m.id
            WHERE (cs_module.company_id = :company_id OR cs_pack.company_id = :company_id)
            AND (cs_module.status = 'active' OR cs_pack.status = 'active')
            AND (cs_module.end_date IS NULL OR cs_module.end_date >= CURDATE())
            AND (cs_pack.end_date IS NULL OR cs_pack.end_date >= CURDATE())
            ORDER BY m.sort_order ASC");

        $this->db->bind(':company_id', $companyId);
        return $this->db->fetchAll();
    }

    /**
     * Check if company has access to a module
     */
    public function hasModuleAccess($companyId, $moduleCode) {
        $this->db->query("SELECT COUNT(*) as count
            FROM subscription_modules m
            LEFT JOIN company_subscriptions cs_module ON cs_module.module_id = m.id AND cs_module.status = 'active'
            LEFT JOIN company_subscriptions cs_pack ON cs_pack.pack_id IS NOT NULL AND cs_pack.status = 'active'
            LEFT JOIN subscription_pack_modules pm ON pm.pack_id = cs_pack.pack_id AND pm.module_id = m.id
            WHERE m.module_code = :module_code
            AND (cs_module.company_id = :company_id OR cs_pack.company_id = :company_id)
            AND (cs_module.status = 'active' OR cs_pack.status = 'active')
            AND (cs_module.end_date IS NULL OR cs_module.end_date >= CURDATE())
            AND (cs_pack.end_date IS NULL OR cs_pack.end_date >= CURDATE())");

        $this->db->bind(':company_id', $companyId);
        $this->db->bind(':module_code', $moduleCode);

        $result = $this->db->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get all subscriptions for a company
     */
    public function getCompanySubscriptions($companyId, $activeOnly = false) {
        $sql = "SELECT cs.*,
                m.module_name, m.module_code,
                p.pack_name, p.pack_code
                FROM company_subscriptions cs
                LEFT JOIN subscription_modules m ON cs.module_id = m.id
                LEFT JOIN subscription_packs p ON cs.pack_id = p.id
                WHERE cs.company_id = :company_id";

        if ($activeOnly) {
            $sql .= " AND cs.status = 'active'";
        }

        $sql .= " ORDER BY cs.created_at DESC";

        $this->db->query($sql);
        $this->db->bind(':company_id', $companyId);
        return $this->db->fetchAll();
    }

    /**
     * Subscribe company to a module
     */
    public function subscribeToModule($companyId, $moduleId, $billingCycle = 'monthly', $startDate = null) {
        $module = $this->getModuleById($moduleId);
        if (!$module) {
            return false;
        }

        $price = $billingCycle === 'yearly' ? $module['price_yearly'] : $module['price_monthly'];
        $startDate = $startDate ?? date('Y-m-d');
        $nextBillingDate = $this->calculateNextBillingDate($startDate, $billingCycle);

        $this->db->query("INSERT INTO company_subscriptions
            (company_id, subscription_type, module_id, billing_cycle, price, start_date, next_billing_date, status)
            VALUES (:company_id, 'module', :module_id, :billing_cycle, :price, :start_date, :next_billing_date, 'active')");

        $this->db->bind(':company_id', $companyId);
        $this->db->bind(':module_id', $moduleId);
        $this->db->bind(':billing_cycle', $billingCycle);
        $this->db->bind(':price', $price);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':next_billing_date', $nextBillingDate);

        if ($this->db->execute()) {
            $subscriptionId = $this->db->lastInsertId();

            // Log the action
            $this->logSubscriptionAction($companyId, $subscriptionId, 'activated', "Subscribed to module: {$module['module_name']}");

            return $subscriptionId;
        }

        return false;
    }

    /**
     * Subscribe company to a pack
     */
    public function subscribeToPack($companyId, $packId, $billingCycle = 'monthly', $startDate = null) {
        $pack = $this->getPackById($packId);
        if (!$pack) {
            return false;
        }

        $price = $billingCycle === 'yearly' ? $pack['price_yearly'] : $pack['price_monthly'];
        $startDate = $startDate ?? date('Y-m-d');
        $nextBillingDate = $this->calculateNextBillingDate($startDate, $billingCycle);

        $this->db->query("INSERT INTO company_subscriptions
            (company_id, subscription_type, pack_id, billing_cycle, price, start_date, next_billing_date, status)
            VALUES (:company_id, 'pack', :pack_id, :billing_cycle, :price, :start_date, :next_billing_date, 'active')");

        $this->db->bind(':company_id', $companyId);
        $this->db->bind(':pack_id', $packId);
        $this->db->bind(':billing_cycle', $billingCycle);
        $this->db->bind(':price', $price);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':next_billing_date', $nextBillingDate);

        if ($this->db->execute()) {
            $subscriptionId = $this->db->lastInsertId();

            // Log the action
            $this->logSubscriptionAction($companyId, $subscriptionId, 'activated', "Subscribed to pack: {$pack['pack_name']}");

            return $subscriptionId;
        }

        return false;
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription($subscriptionId, $endDate = null) {
        $endDate = $endDate ?? date('Y-m-d');

        $this->db->query("UPDATE company_subscriptions SET
            status = 'cancelled',
            end_date = :end_date,
            auto_renew = 0
            WHERE id = :id");

        $this->db->bind(':id', $subscriptionId);
        $this->db->bind(':end_date', $endDate);

        if ($this->db->execute()) {
            // Get subscription details for logging
            $this->db->query("SELECT * FROM company_subscriptions WHERE id = :id");
            $this->db->bind(':id', $subscriptionId);
            $subscription = $this->db->fetch();

            $this->logSubscriptionAction($subscription['company_id'], $subscriptionId, 'cancelled', "Subscription cancelled");

            return true;
        }

        return false;
    }

    /**
     * Suspend a subscription
     */
    public function suspendSubscription($subscriptionId, $reason = null) {
        $this->db->query("UPDATE company_subscriptions SET
            status = 'suspended',
            notes = :notes
            WHERE id = :id");

        $this->db->bind(':id', $subscriptionId);
        $this->db->bind(':notes', $reason);

        if ($this->db->execute()) {
            $this->db->query("SELECT * FROM company_subscriptions WHERE id = :id");
            $this->db->bind(':id', $subscriptionId);
            $subscription = $this->db->fetch();

            $this->logSubscriptionAction($subscription['company_id'], $subscriptionId, 'suspended', $reason ?? "Subscription suspended");

            return true;
        }

        return false;
    }

    /**
     * Reactivate a suspended subscription
     */
    public function reactivateSubscription($subscriptionId) {
        $this->db->query("UPDATE company_subscriptions SET
            status = 'active'
            WHERE id = :id");

        $this->db->bind(':id', $subscriptionId);

        if ($this->db->execute()) {
            $this->db->query("SELECT * FROM company_subscriptions WHERE id = :id");
            $this->db->bind(':id', $subscriptionId);
            $subscription = $this->db->fetch();

            $this->logSubscriptionAction($subscription['company_id'], $subscriptionId, 'reactivated', "Subscription reactivated");

            return true;
        }

        return false;
    }

    // ========================================
    // INVOICING
    // ========================================

    /**
     * Generate invoice for subscription
     */
    public function generateInvoice($subscriptionId) {
        // Get subscription details
        $this->db->query("SELECT cs.*,
            m.module_name,
            p.pack_name
            FROM company_subscriptions cs
            LEFT JOIN subscription_modules m ON cs.module_id = m.id
            LEFT JOIN subscription_packs p ON cs.pack_id = p.id
            WHERE cs.id = :id");

        $this->db->bind(':id', $subscriptionId);
        $subscription = $this->db->fetch();

        if (!$subscription) {
            return false;
        }

        $invoiceNumber = $this->generateInvoiceNumber();
        $invoiceDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+15 days'));

        $amount = $subscription['price'];
        $taxAmount = $amount * 0.20; // 20% VAT
        $totalAmount = $amount + $taxAmount;

        $itemName = $subscription['subscription_type'] === 'module'
            ? $subscription['module_name']
            : $subscription['pack_name'];

        $items = json_encode([
            [
                'description' => $itemName,
                'quantity' => 1,
                'unit_price' => $amount,
                'total' => $amount
            ]
        ]);

        $this->db->query("INSERT INTO subscription_invoices
            (invoice_number, company_id, subscription_id, invoice_date, due_date, amount, tax_amount, total_amount, items, status)
            VALUES (:invoice_number, :company_id, :subscription_id, :invoice_date, :due_date, :amount, :tax_amount, :total_amount, :items, 'sent')");

        $this->db->bind(':invoice_number', $invoiceNumber);
        $this->db->bind(':company_id', $subscription['company_id']);
        $this->db->bind(':subscription_id', $subscriptionId);
        $this->db->bind(':invoice_date', $invoiceDate);
        $this->db->bind(':due_date', $dueDate);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':tax_amount', $taxAmount);
        $this->db->bind(':total_amount', $totalAmount);
        $this->db->bind(':items', $items);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get invoices for a company
     */
    public function getCompanyInvoices($companyId, $status = null) {
        $sql = "SELECT * FROM subscription_invoices WHERE company_id = :company_id";

        if ($status) {
            $sql .= " AND status = :status";
        }

        $sql .= " ORDER BY invoice_date DESC";

        $this->db->query($sql);
        $this->db->bind(':company_id', $companyId);

        if ($status) {
            $this->db->bind(':status', $status);
        }

        return $this->db->fetchAll();
    }

    /**
     * Mark invoice as paid
     */
    public function markInvoicePaid($invoiceId, $paymentMethod = null, $paymentReference = null) {
        $this->db->query("UPDATE subscription_invoices SET
            status = 'paid',
            paid_date = CURDATE(),
            payment_method = :payment_method,
            payment_reference = :payment_reference
            WHERE id = :id");

        $this->db->bind(':id', $invoiceId);
        $this->db->bind(':payment_method', $paymentMethod);
        $this->db->bind(':payment_reference', $paymentReference);

        return $this->db->execute();
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Calculate next billing date
     */
    private function calculateNextBillingDate($startDate, $billingCycle) {
        if ($billingCycle === 'yearly') {
            return date('Y-m-d', strtotime($startDate . ' +1 year'));
        } else {
            return date('Y-m-d', strtotime($startDate . ' +1 month'));
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber() {
        $year = date('Y');
        $month = date('m');

        $this->db->query("SELECT MAX(CAST(SUBSTRING(invoice_number, 10) AS UNSIGNED)) as max_num
            FROM subscription_invoices
            WHERE invoice_number LIKE :prefix");

        $prefix = "INV{$year}{$month}%";
        $this->db->bind(':prefix', $prefix);

        $result = $this->db->fetch();
        $nextNum = ($result['max_num'] ?? 0) + 1;

        return sprintf("INV%s%s%04d", $year, $month, $nextNum);
    }

    /**
     * Log subscription action
     */
    private function logSubscriptionAction($companyId, $subscriptionId, $action, $description, $oldValue = null, $newValue = null) {
        $this->db->query("INSERT INTO subscription_history
            (company_id, subscription_id, action, description, old_value, new_value)
            VALUES (:company_id, :subscription_id, :action, :description, :old_value, :new_value)");

        $this->db->bind(':company_id', $companyId);
        $this->db->bind(':subscription_id', $subscriptionId);
        $this->db->bind(':action', $action);
        $this->db->bind(':description', $description);
        $this->db->bind(':old_value', $oldValue);
        $this->db->bind(':new_value', $newValue);

        return $this->db->execute();
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats($companyId = null) {
        if ($companyId) {
            $this->db->query("SELECT
                COUNT(*) as total_subscriptions,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_subscriptions,
                SUM(CASE WHEN status = 'active' THEN price ELSE 0 END) as monthly_revenue
                FROM company_subscriptions
                WHERE company_id = :company_id");

            $this->db->bind(':company_id', $companyId);
        } else {
            $this->db->query("SELECT
                COUNT(*) as total_subscriptions,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_subscriptions,
                SUM(CASE WHEN status = 'active' AND billing_cycle = 'monthly' THEN price ELSE 0 END) as monthly_revenue,
                COUNT(DISTINCT company_id) as total_companies
                FROM company_subscriptions");
        }

        return $this->db->fetch();
    }

    /**
     * Get subscription by ID
     */
    public function getSubscriptionById($id) {
        $this->db->query("SELECT * FROM company_subscriptions WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get subscription by ID and company
     */
    public function getSubscriptionByIdAndCompany($id, $companyId) {
        $this->db->query("SELECT * FROM company_subscriptions WHERE id = :id AND company_id = :company_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':company_id', $companyId);
        return $this->db->fetch();
    }

    /**
     * Get invoice by ID
     */
    public function getInvoiceById($id) {
        $this->db->query("SELECT * FROM subscription_invoices WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get invoice by ID and company
     */
    public function getInvoiceByIdAndCompany($id, $companyId) {
        $this->db->query("SELECT * FROM subscription_invoices WHERE id = :id AND company_id = :company_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':company_id', $companyId);
        return $this->db->fetch();
    }
}
