<?php
/**
 * Driver Model - Multi-tenant enabled
 */

class Driver {
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
    private function getCompanyFilter($tableAlias = 'd') {
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

    /**
     * Get all drivers
     */
    public function getAllDrivers() {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT * FROM drivers d WHERE {$filter} ORDER BY d.last_name, d.first_name");
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Get driver by ID
     */
    public function getDriverById($id) {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT * FROM drivers d WHERE d.id = :id AND {$filter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    /**
     * Get drivers by status
     */
    public function getDriversByStatus($status) {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT * FROM drivers d WHERE d.status = :status AND {$filter} ORDER BY d.last_name, d.first_name");
        $this->db->bind(':status', $status);
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Create new driver
     */
    public function createDriver($data) {
        // Check driver limit
        if (!isSuperAdmin() && hasReachedDriverLimit()) {
            return false;
        }

        $this->db->query("INSERT INTO drivers (
            company_id, first_name, last_name, email, phone, mobile,
            date_of_birth, place_of_birth, nationality,
            address, city, state, postal_code, country,
            license_number, license_type, license_issue_date, license_expiry_date,
            hire_date, contract_type, salary,
            emergency_contact_name, emergency_contact_phone, emergency_contact_relation,
            status, notes, photo
        ) VALUES (
            :company_id, :first_name, :last_name, :email, :phone, :mobile,
            :date_of_birth, :place_of_birth, :nationality,
            :address, :city, :state, :postal_code, :country,
            :license_number, :license_type, :license_issue_date, :license_expiry_date,
            :hire_date, :contract_type, :salary,
            :emergency_contact_name, :emergency_contact_phone, :emergency_contact_relation,
            :status, :notes, :photo
        )");

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':mobile', $data['mobile'] ?? null);
        $this->db->bind(':date_of_birth', $data['date_of_birth'] ?? null);
        $this->db->bind(':place_of_birth', $data['place_of_birth'] ?? null);
        $this->db->bind(':nationality', $data['nationality'] ?? 'Tunisian');
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':city', $data['city'] ?? null);
        $this->db->bind(':state', $data['state'] ?? null);
        $this->db->bind(':postal_code', $data['postal_code'] ?? null);
        $this->db->bind(':country', $data['country'] ?? 'Tunisia');
        $this->db->bind(':license_number', $data['license_number']);
        $this->db->bind(':license_type', $data['license_type'] ?? 'B');
        $this->db->bind(':license_issue_date', $data['license_issue_date'] ?? null);
        $this->db->bind(':license_expiry_date', $data['license_expiry_date'] ?? null);
        $this->db->bind(':hire_date', $data['hire_date'] ?? date('Y-m-d'));
        $this->db->bind(':contract_type', $data['contract_type'] ?? 'full_time');
        $this->db->bind(':salary', $data['salary'] ?? null);
        $this->db->bind(':emergency_contact_name', $data['emergency_contact_name'] ?? null);
        $this->db->bind(':emergency_contact_phone', $data['emergency_contact_phone'] ?? null);
        $this->db->bind(':emergency_contact_relation', $data['emergency_contact_relation'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':photo', $data['photo'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update driver
     */
    public function updateDriver($id, $data) {
        $filter = $this->getCompanyFilter('d');

        $this->db->query("UPDATE drivers d SET
            d.first_name = :first_name,
            d.last_name = :last_name,
            d.email = :email,
            d.phone = :phone,
            d.mobile = :mobile,
            d.date_of_birth = :date_of_birth,
            d.place_of_birth = :place_of_birth,
            d.nationality = :nationality,
            d.address = :address,
            d.city = :city,
            d.state = :state,
            d.postal_code = :postal_code,
            d.country = :country,
            d.license_number = :license_number,
            d.license_type = :license_type,
            d.license_issue_date = :license_issue_date,
            d.license_expiry_date = :license_expiry_date,
            d.hire_date = :hire_date,
            d.contract_type = :contract_type,
            d.salary = :salary,
            d.emergency_contact_name = :emergency_contact_name,
            d.emergency_contact_phone = :emergency_contact_phone,
            d.emergency_contact_relation = :emergency_contact_relation,
            d.status = :status,
            d.notes = :notes,
            d.photo = :photo
            WHERE d.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':mobile', $data['mobile'] ?? null);
        $this->db->bind(':date_of_birth', $data['date_of_birth'] ?? null);
        $this->db->bind(':place_of_birth', $data['place_of_birth'] ?? null);
        $this->db->bind(':nationality', $data['nationality'] ?? 'Tunisian');
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':city', $data['city'] ?? null);
        $this->db->bind(':state', $data['state'] ?? null);
        $this->db->bind(':postal_code', $data['postal_code'] ?? null);
        $this->db->bind(':country', $data['country'] ?? 'Tunisia');
        $this->db->bind(':license_number', $data['license_number']);
        $this->db->bind(':license_type', $data['license_type'] ?? 'B');
        $this->db->bind(':license_issue_date', $data['license_issue_date'] ?? null);
        $this->db->bind(':license_expiry_date', $data['license_expiry_date'] ?? null);
        $this->db->bind(':hire_date', $data['hire_date'] ?? date('Y-m-d'));
        $this->db->bind(':contract_type', $data['contract_type'] ?? 'full_time');
        $this->db->bind(':salary', $data['salary'] ?? null);
        $this->db->bind(':emergency_contact_name', $data['emergency_contact_name'] ?? null);
        $this->db->bind(':emergency_contact_phone', $data['emergency_contact_phone'] ?? null);
        $this->db->bind(':emergency_contact_relation', $data['emergency_contact_relation'] ?? null);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':photo', $data['photo'] ?? null);

        return $this->db->execute();
    }

    /**
     * Delete driver
     */
    public function deleteDriver($id) {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("DELETE FROM drivers WHERE id = :id AND {$filter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->execute();
    }

    /**
     * Count total drivers
     */
    public function countDrivers() {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT COUNT(*) as total FROM drivers d WHERE {$filter}");
        $this->bindCompanyId();
        $result = $this->db->fetch();
        return $result['total'];
    }

    /**
     * Count drivers by status
     */
    public function countDriversByStatus($status) {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT COUNT(*) as total FROM drivers d WHERE d.status = :status AND {$filter}");
        $this->db->bind(':status', $status);
        $this->bindCompanyId();
        $result = $this->db->fetch();
        return $result['total'];
    }

    /**
     * Get drivers with expiring licenses
     */
    public function getDriversWithExpiringLicenses($days = 30) {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT * FROM drivers d
            WHERE {$filter}
            AND d.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY d.license_expiry_date ASC");
        $this->bindCompanyId();
        $this->db->bind(':days', $days);
        return $this->db->fetchAll();
    }

    /**
     * Search drivers
     */
    public function searchDrivers($keyword) {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT * FROM drivers d
            WHERE {$filter}
            AND (d.first_name LIKE :keyword
               OR d.last_name LIKE :keyword
               OR d.email LIKE :keyword
               OR d.license_number LIKE :keyword)
            ORDER BY d.last_name, d.first_name");
        $this->bindCompanyId();
        $this->db->bind(':keyword', '%' . $keyword . '%');
        return $this->db->fetchAll();
    }

    /**
     * Get available drivers (active and not currently assigned)
     */
    public function getAvailableDrivers() {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT * FROM drivers d
            WHERE {$filter}
            AND d.status = 'active'
            ORDER BY d.last_name, d.first_name");
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Get dashboard stats
     */
    public function getDashboardStats() {
        $filter = $this->getCompanyFilter('d');
        $this->db->query("SELECT
            COUNT(*) as total_drivers,
            SUM(CASE WHEN d.status = 'active' THEN 1 ELSE 0 END) as active_drivers,
            SUM(CASE WHEN d.status = 'inactive' THEN 1 ELSE 0 END) as inactive_drivers,
            SUM(CASE WHEN d.status = 'on_leave' THEN 1 ELSE 0 END) as on_leave_drivers,
            SUM(CASE WHEN d.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_licenses
            FROM drivers d WHERE {$filter}");
        $this->bindCompanyId();
        return $this->db->fetch();
    }
}
