<?php
/**
 * Drivers & HR Model - Multi-tenant enabled
 * Manages driver profiles and infractions
 */

class Drivers {
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
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    /**
     * Get all driver profiles
     */
    public function getAllDriverProfiles() {
        $companyFilter = $this->getCompanyFilter('dp');
        $this->db->query("SELECT dp.*, u.username, u.email, u.first_name, u.last_name, u.phone, u.status
            FROM driver_profiles dp
            LEFT JOIN users u ON dp.user_id = u.id
            WHERE {$companyFilter}
            ORDER BY u.first_name, u.last_name");
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Get driver profile by ID
     */
    public function getDriverProfileById($id) {
        $companyFilter = $this->getCompanyFilter('dp');
        $this->db->query("SELECT dp.*, u.*
            FROM driver_profiles dp
            LEFT JOIN users u ON dp.user_id = u.id
            WHERE dp.id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    /**
     * Add driver profile
     */
    public function addDriverProfile($data) {
        $this->db->query('INSERT INTO driver_profiles (
            company_id, user_id, license_number, license_type, license_issue_date, license_expiry_date,
            medical_certificate_expiry, emergency_contact_name, emergency_contact_phone,
            blood_type, hire_date, contract_type, salary, bank_account,
            social_security_number, status
        ) VALUES (
            :company_id, :user_id, :license_number, :license_type, :license_issue_date, :license_expiry_date,
            :medical_certificate_expiry, :emergency_contact_name, :emergency_contact_phone,
            :blood_type, :hire_date, :contract_type, :salary, :bank_account,
            :social_security_number, :status
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':license_number', $data['license_number']);
        $this->db->bind(':license_type', $data['license_type'] ?? null);
        $this->db->bind(':license_issue_date', $data['license_issue_date'] ?? null);
        $this->db->bind(':license_expiry_date', $data['license_expiry_date'] ?? null);
        $this->db->bind(':medical_certificate_expiry', $data['medical_certificate_expiry'] ?? null);
        $this->db->bind(':emergency_contact_name', $data['emergency_contact_name'] ?? null);
        $this->db->bind(':emergency_contact_phone', $data['emergency_contact_phone'] ?? null);
        $this->db->bind(':blood_type', $data['blood_type'] ?? null);
        $this->db->bind(':hire_date', $data['hire_date'] ?? null);
        $this->db->bind(':contract_type', $data['contract_type']);
        $this->db->bind(':salary', $data['salary'] ?? null);
        $this->db->bind(':bank_account', $data['bank_account'] ?? null);
        $this->db->bind(':social_security_number', $data['social_security_number'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update driver profile
     */
    public function updateDriverProfile($id, $data) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE driver_profiles SET
            license_number = :license_number,
            license_type = :license_type,
            license_issue_date = :license_issue_date,
            license_expiry_date = :license_expiry_date,
            medical_certificate_expiry = :medical_certificate_expiry,
            emergency_contact_name = :emergency_contact_name,
            emergency_contact_phone = :emergency_contact_phone,
            blood_type = :blood_type,
            hire_date = :hire_date,
            contract_type = :contract_type,
            salary = :salary,
            bank_account = :bank_account,
            social_security_number = :social_security_number,
            status = :status
            WHERE id = :id AND {$companyFilter}");

        $this->db->bind(':id', $id);
        $this->db->bind(':license_number', $data['license_number']);
        $this->db->bind(':license_type', $data['license_type'] ?? null);
        $this->db->bind(':license_issue_date', $data['license_issue_date'] ?? null);
        $this->db->bind(':license_expiry_date', $data['license_expiry_date'] ?? null);
        $this->db->bind(':medical_certificate_expiry', $data['medical_certificate_expiry'] ?? null);
        $this->db->bind(':emergency_contact_name', $data['emergency_contact_name'] ?? null);
        $this->db->bind(':emergency_contact_phone', $data['emergency_contact_phone'] ?? null);
        $this->db->bind(':blood_type', $data['blood_type'] ?? null);
        $this->db->bind(':hire_date', $data['hire_date'] ?? null);
        $this->db->bind(':contract_type', $data['contract_type']);
        $this->db->bind(':salary', $data['salary'] ?? null);
        $this->db->bind(':bank_account', $data['bank_account'] ?? null);
        $this->db->bind(':social_security_number', $data['social_security_number'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'active');
        $this->bindCompanyId();

        return $this->db->execute();
    }

    /**
     * Delete driver profile
     */
    public function deleteDriverProfile($id) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("DELETE FROM driver_profiles WHERE id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->execute();
    }

    /**
     * Get driver infractions
     */
    public function getDriverInfractions($driverId = null) {
        $companyFilter = $this->getCompanyFilter('di');
        $sql = "SELECT di.*, d.first_name, d.last_name, v.registration_number
            FROM driver_infractions di
            LEFT JOIN users d ON di.driver_id = d.id
            LEFT JOIN vehicles v ON di.vehicle_id = v.id
            WHERE {$companyFilter}";

        if ($driverId) {
            $sql .= ' AND di.driver_id = :driver_id';
        }

        $sql .= ' ORDER BY di.infraction_date DESC';

        $this->db->query($sql);
        $this->bindCompanyId();

        if ($driverId) {
            $this->db->bind(':driver_id', $driverId);
        }

        return $this->db->fetchAll();
    }

    /**
     * Get infraction by ID
     */
    public function getInfractionById($id) {
        $companyFilter = $this->getCompanyFilter('di');
        $this->db->query("SELECT di.*, d.first_name, d.last_name, v.registration_number
            FROM driver_infractions di
            LEFT JOIN users d ON di.driver_id = d.id
            LEFT JOIN vehicles v ON di.vehicle_id = v.id
            WHERE di.id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    /**
     * Add infraction
     */
    public function addInfraction($data) {
        $this->db->query('INSERT INTO driver_infractions (
            company_id, driver_id, vehicle_id, infraction_date, infraction_type, description,
            location, fine_amount, points_deducted, paid, notes, created_by
        ) VALUES (
            :company_id, :driver_id, :vehicle_id, :infraction_date, :infraction_type, :description,
            :location, :fine_amount, :points_deducted, :paid, :notes, :created_by
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':driver_id', $data['driver_id']);
        $this->db->bind(':vehicle_id', $data['vehicle_id'] ?? null);
        $this->db->bind(':infraction_date', $data['infraction_date']);
        $this->db->bind(':infraction_type', $data['infraction_type']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':location', $data['location'] ?? null);
        $this->db->bind(':fine_amount', $data['fine_amount'] ?? 0);
        $this->db->bind(':points_deducted', $data['points_deducted'] ?? 0);
        $this->db->bind(':paid', $data['paid'] ?? false);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':created_by', $_SESSION['user_id']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update infraction
     */
    public function updateInfraction($id, $data) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE driver_infractions SET
            driver_id = :driver_id,
            vehicle_id = :vehicle_id,
            infraction_date = :infraction_date,
            infraction_type = :infraction_type,
            description = :description,
            location = :location,
            fine_amount = :fine_amount,
            points_deducted = :points_deducted,
            paid = :paid,
            notes = :notes
            WHERE id = :id AND {$companyFilter}");

        $this->db->bind(':id', $id);
        $this->db->bind(':driver_id', $data['driver_id']);
        $this->db->bind(':vehicle_id', $data['vehicle_id'] ?? null);
        $this->db->bind(':infraction_date', $data['infraction_date']);
        $this->db->bind(':infraction_type', $data['infraction_type']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':location', $data['location'] ?? null);
        $this->db->bind(':fine_amount', $data['fine_amount'] ?? 0);
        $this->db->bind(':points_deducted', $data['points_deducted'] ?? 0);
        $this->db->bind(':paid', $data['paid'] ?? false);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->bindCompanyId();

        return $this->db->execute();
    }

    /**
     * Mark infraction as paid
     */
    public function markInfractionPaid($id) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("UPDATE driver_infractions SET paid = 1 WHERE id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->execute();
    }

    /**
     * Delete infraction
     */
    public function deleteInfraction($id) {
        $companyFilter = $this->getCompanyFilter('');
        $this->db->query("DELETE FROM driver_infractions WHERE id = :id AND {$companyFilter}");
        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->execute();
    }

    /**
     * Get expiring licenses
     */
    public function getExpiringLicenses($days = 30) {
        $companyFilter = $this->getCompanyFilter('dp');
        $this->db->query("SELECT dp.*, u.first_name, u.last_name, u.email, u.phone
            FROM driver_profiles dp
            LEFT JOIN users u ON dp.user_id = u.id
            WHERE {$companyFilter}
            AND dp.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY dp.license_expiry_date");
        $this->bindCompanyId();
        $this->db->bind(':days', $days);
        return $this->db->fetchAll();
    }

    /**
     * Get expiring medical certificates
     */
    public function getExpiringMedicalCertificates($days = 30) {
        $companyFilter = $this->getCompanyFilter('dp');
        $this->db->query("SELECT dp.*, u.first_name, u.last_name, u.email, u.phone
            FROM driver_profiles dp
            LEFT JOIN users u ON dp.user_id = u.id
            WHERE {$companyFilter}
            AND dp.medical_certificate_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY dp.medical_certificate_expiry");
        $this->bindCompanyId();
        $this->db->bind(':days', $days);
        return $this->db->fetchAll();
    }

    /**
     * Get driver infractions statistics
     */
    public function getInfractionStats($driverId = null) {
        $companyFilter = $this->getCompanyFilter('di');
        $sql = "SELECT
            COUNT(*) as total_infractions,
            SUM(fine_amount) as total_fines,
            SUM(CASE WHEN paid = 1 THEN fine_amount ELSE 0 END) as paid_fines,
            SUM(CASE WHEN paid = 0 THEN fine_amount ELSE 0 END) as unpaid_fines,
            SUM(points_deducted) as total_points
            FROM driver_infractions di
            WHERE {$companyFilter}";

        if ($driverId) {
            $sql .= ' AND di.driver_id = :driver_id';
        }

        $this->db->query($sql);
        $this->bindCompanyId();

        if ($driverId) {
            $this->db->bind(':driver_id', $driverId);
        }

        return $this->db->fetch();
    }
}
