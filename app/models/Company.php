<?php
/**
 * Company Model - For managing multi-tenant companies
 * Used primarily by super admins
 */

class Company {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all companies
     */
    public function getAllCompanies($status = null) {
        $sql = "SELECT * FROM companies";

        if ($status) {
            $sql .= " WHERE status = :status";
        }

        $sql .= " ORDER BY created_at DESC";

        $this->db->query($sql);

        if ($status) {
            $this->db->bind(':status', $status);
        }

        return $this->db->fetchAll();
    }

    /**
     * Get company by ID
     */
    public function getCompanyById($id) {
        $this->db->query("SELECT * FROM companies WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get company by code
     */
    public function getCompanyByCode($code) {
        $this->db->query("SELECT * FROM companies WHERE company_code = :code");
        $this->db->bind(':code', $code);
        return $this->db->fetch();
    }

    /**
     * Create new company
     */
    public function createCompany($data) {
        // Generate company code if not provided
        if (empty($data['company_code'])) {
            $data['company_code'] = strtoupper(substr($data['company_name'], 0, 3)) .
                                   date('Y') .
                                   str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }

        $this->db->query("INSERT INTO companies (
            company_name, company_code, legal_name, tax_id, registration_number, industry, company_type,
            email, phone, website, address, city, state, postal_code, country,
            billing_email, billing_address, billing_contact, billing_phone,
            timezone, currency, language, date_format, time_format,
            subscription_status, trial_ends_at, max_users, max_vehicles, max_drivers,
            logo, primary_color, secondary_color,
            status, notes, created_by
        ) VALUES (
            :company_name, :company_code, :legal_name, :tax_id, :registration_number, :industry, :company_type,
            :email, :phone, :website, :address, :city, :state, :postal_code, :country,
            :billing_email, :billing_address, :billing_contact, :billing_phone,
            :timezone, :currency, :language, :date_format, :time_format,
            :subscription_status, :trial_ends_at, :max_users, :max_vehicles, :max_drivers,
            :logo, :primary_color, :secondary_color,
            :status, :notes, :created_by
        )");

        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':company_code', $data['company_code']);
        $this->db->bind(':legal_name', $data['legal_name'] ?? null);
        $this->db->bind(':tax_id', $data['tax_id'] ?? null);
        $this->db->bind(':registration_number', $data['registration_number'] ?? null);
        $this->db->bind(':industry', $data['industry'] ?? null);
        $this->db->bind(':company_type', $data['company_type'] ?? 'fleet');

        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':website', $data['website'] ?? null);
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':city', $data['city'] ?? null);
        $this->db->bind(':state', $data['state'] ?? null);
        $this->db->bind(':postal_code', $data['postal_code'] ?? null);
        $this->db->bind(':country', $data['country'] ?? 'Tunisia');

        $this->db->bind(':billing_email', $data['billing_email'] ?? $data['email'] ?? null);
        $this->db->bind(':billing_address', $data['billing_address'] ?? $data['address'] ?? null);
        $this->db->bind(':billing_contact', $data['billing_contact'] ?? null);
        $this->db->bind(':billing_phone', $data['billing_phone'] ?? $data['phone'] ?? null);

        $this->db->bind(':timezone', $data['timezone'] ?? 'Africa/Tunis');
        $this->db->bind(':currency', $data['currency'] ?? 'TND');
        $this->db->bind(':language', $data['language'] ?? 'fr');
        $this->db->bind(':date_format', $data['date_format'] ?? 'd/m/Y');
        $this->db->bind(':time_format', $data['time_format'] ?? 'H:i');

        $this->db->bind(':subscription_status', $data['subscription_status'] ?? 'trial');
        $this->db->bind(':trial_ends_at', $data['trial_ends_at'] ?? date('Y-m-d', strtotime('+30 days')));
        $this->db->bind(':max_users', $data['max_users'] ?? 5);
        $this->db->bind(':max_vehicles', $data['max_vehicles'] ?? 10);
        $this->db->bind(':max_drivers', $data['max_drivers'] ?? 10);

        $this->db->bind(':logo', $data['logo'] ?? null);
        $this->db->bind(':primary_color', $data['primary_color'] ?? '#007bff');
        $this->db->bind(':secondary_color', $data['secondary_color'] ?? '#6c757d');

        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':created_by', $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update company
     */
    public function updateCompany($id, $data) {
        $this->db->query("UPDATE companies SET
            company_name = :company_name,
            legal_name = :legal_name,
            tax_id = :tax_id,
            registration_number = :registration_number,
            industry = :industry,
            company_type = :company_type,
            email = :email,
            phone = :phone,
            website = :website,
            address = :address,
            city = :city,
            state = :state,
            postal_code = :postal_code,
            country = :country,
            billing_email = :billing_email,
            billing_address = :billing_address,
            billing_contact = :billing_contact,
            billing_phone = :billing_phone,
            timezone = :timezone,
            currency = :currency,
            language = :language,
            date_format = :date_format,
            time_format = :time_format,
            max_users = :max_users,
            max_vehicles = :max_vehicles,
            max_drivers = :max_drivers,
            logo = :logo,
            primary_color = :primary_color,
            secondary_color = :secondary_color,
            notes = :notes
            WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':company_name', $data['company_name']);
        $this->db->bind(':legal_name', $data['legal_name'] ?? null);
        $this->db->bind(':tax_id', $data['tax_id'] ?? null);
        $this->db->bind(':registration_number', $data['registration_number'] ?? null);
        $this->db->bind(':industry', $data['industry'] ?? null);
        $this->db->bind(':company_type', $data['company_type'] ?? 'fleet');

        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':website', $data['website'] ?? null);
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':city', $data['city'] ?? null);
        $this->db->bind(':state', $data['state'] ?? null);
        $this->db->bind(':postal_code', $data['postal_code'] ?? null);
        $this->db->bind(':country', $data['country'] ?? 'Tunisia');

        $this->db->bind(':billing_email', $data['billing_email'] ?? null);
        $this->db->bind(':billing_address', $data['billing_address'] ?? null);
        $this->db->bind(':billing_contact', $data['billing_contact'] ?? null);
        $this->db->bind(':billing_phone', $data['billing_phone'] ?? null);

        $this->db->bind(':timezone', $data['timezone'] ?? 'Africa/Tunis');
        $this->db->bind(':currency', $data['currency'] ?? 'TND');
        $this->db->bind(':language', $data['language'] ?? 'fr');
        $this->db->bind(':date_format', $data['date_format'] ?? 'd/m/Y');
        $this->db->bind(':time_format', $data['time_format'] ?? 'H:i');

        $this->db->bind(':max_users', $data['max_users'] ?? 5);
        $this->db->bind(':max_vehicles', $data['max_vehicles'] ?? 10);
        $this->db->bind(':max_drivers', $data['max_drivers'] ?? 10);

        $this->db->bind(':logo', $data['logo'] ?? null);
        $this->db->bind(':primary_color', $data['primary_color'] ?? '#007bff');
        $this->db->bind(':secondary_color', $data['secondary_color'] ?? '#6c757d');

        $this->db->bind(':notes', $data['notes'] ?? null);

        return $this->db->execute();
    }

    /**
     * Update subscription status
     */
    public function updateSubscriptionStatus($id, $status, $trialEndsAt = null) {
        $this->db->query("UPDATE companies SET
            subscription_status = :status,
            trial_ends_at = :trial_ends_at
            WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        $this->db->bind(':trial_ends_at', $trialEndsAt);

        return $this->db->execute();
    }

    /**
     * Update company limits
     */
    public function updateLimits($id, $maxUsers, $maxVehicles, $maxDrivers) {
        $this->db->query("UPDATE companies SET
            max_users = :max_users,
            max_vehicles = :max_vehicles,
            max_drivers = :max_drivers
            WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':max_users', $maxUsers);
        $this->db->bind(':max_vehicles', $maxVehicles);
        $this->db->bind(':max_drivers', $maxDrivers);

        return $this->db->execute();
    }

    /**
     * Update company status
     */
    public function updateStatus($id, $status) {
        $this->db->query("UPDATE companies SET status = :status WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    /**
     * Delete company (soft delete - set status to deleted)
     */
    public function deleteCompany($id) {
        return $this->updateStatus($id, 'deleted');
    }

    /**
     * Get company stats
     */
    public function getCompanyStats($id) {
        // Get counts of resources
        $this->db->query("SELECT
            (SELECT COUNT(*) FROM users WHERE company_id = :id) as total_users,
            (SELECT COUNT(*) FROM vehicles WHERE company_id = :id) as total_vehicles,
            (SELECT COUNT(*) FROM drivers WHERE company_id = :id) as total_drivers,
            (SELECT COUNT(*) FROM missions WHERE company_id = :id) as total_missions,
            (SELECT COUNT(*) FROM fuel_transactions WHERE company_id = :id) as total_fuel_transactions
        ");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    /**
     * Get all companies with stats
     */
    public function getCompaniesWithStats() {
        $this->db->query("SELECT
            c.*,
            (SELECT COUNT(*) FROM users WHERE company_id = c.id) as total_users,
            (SELECT COUNT(*) FROM vehicles WHERE company_id = c.id) as total_vehicles,
            (SELECT COUNT(*) FROM drivers WHERE company_id = c.id) as total_drivers
            FROM companies c
            WHERE c.status != 'deleted'
            ORDER BY c.created_at DESC");

        return $this->db->fetchAll();
    }

    /**
     * Count companies by status
     */
    public function countByStatus($status) {
        $this->db->query("SELECT COUNT(*) as total FROM companies WHERE status = :status");
        $this->db->bind(':status', $status);
        $result = $this->db->fetch();
        return $result['total'];
    }

    /**
     * Get companies with expiring trials
     */
    public function getExpiringTrials($days = 7) {
        $this->db->query("SELECT * FROM companies
            WHERE subscription_status = 'trial'
            AND trial_ends_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY trial_ends_at ASC");
        $this->db->bind(':days', $days);
        return $this->db->fetchAll();
    }
}
