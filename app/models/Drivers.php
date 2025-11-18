<?php
/**
 * Drivers & HR Model
 */

class Drivers extends Database {

    public function __construct() {
        parent::__construct();
    }

    public function getAllDriverProfiles() {
        $this->query('SELECT dp.*, u.username, u.email, u.first_name, u.last_name, u.phone, u.status
            FROM driver_profiles dp
            LEFT JOIN users u ON dp.user_id = u.id
            ORDER BY u.first_name, u.last_name');
        return $this->fetchAll();
    }

    public function getDriverProfileById($id) {
        $this->query('SELECT dp.*, u.*
            FROM driver_profiles dp
            LEFT JOIN users u ON dp.user_id = u.id
            WHERE dp.id = :id');
        $this->bind(':id', $id);
        return $this->fetch();
    }

    public function addDriverProfile($data) {
        $this->query('INSERT INTO driver_profiles (
            user_id, license_number, license_type, license_issue_date, license_expiry_date,
            medical_certificate_expiry, emergency_contact_name, emergency_contact_phone,
            blood_type, hire_date, contract_type, salary, bank_account,
            social_security_number, status
        ) VALUES (
            :user_id, :license_number, :license_type, :license_issue_date, :license_expiry_date,
            :medical_certificate_expiry, :emergency_contact_name, :emergency_contact_phone,
            :blood_type, :hire_date, :contract_type, :salary, :bank_account,
            :social_security_number, :status
        )');

        $this->bind(':user_id', $data['user_id']);
        $this->bind(':license_number', $data['license_number']);
        $this->bind(':license_type', $data['license_type'] ?? null);
        $this->bind(':license_issue_date', $data['license_issue_date'] ?? null);
        $this->bind(':license_expiry_date', $data['license_expiry_date'] ?? null);
        $this->bind(':medical_certificate_expiry', $data['medical_certificate_expiry'] ?? null);
        $this->bind(':emergency_contact_name', $data['emergency_contact_name'] ?? null);
        $this->bind(':emergency_contact_phone', $data['emergency_contact_phone'] ?? null);
        $this->bind(':blood_type', $data['blood_type'] ?? null);
        $this->bind(':hire_date', $data['hire_date'] ?? null);
        $this->bind(':contract_type', $data['contract_type']);
        $this->bind(':salary', $data['salary'] ?? null);
        $this->bind(':bank_account', $data['bank_account'] ?? null);
        $this->bind(':social_security_number', $data['social_security_number'] ?? null);
        $this->bind(':status', $data['status'] ?? 'active');

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function getDriverInfractions($driverId = null) {
        $sql = 'SELECT di.*, d.first_name, d.last_name, v.registration_number
            FROM driver_infractions di
            LEFT JOIN users d ON di.driver_id = d.id
            LEFT JOIN vehicles v ON di.vehicle_id = v.id';

        if ($driverId) {
            $sql .= ' WHERE di.driver_id = :driver_id';
        }

        $sql .= ' ORDER BY di.infraction_date DESC';

        $this->query($sql);

        if ($driverId) {
            $this->bind(':driver_id', $driverId);
        }

        return $this->fetchAll();
    }

    public function addInfraction($data) {
        $this->query('INSERT INTO driver_infractions (
            driver_id, vehicle_id, infraction_date, infraction_type, description,
            location, fine_amount, points_deducted, paid, notes, created_by
        ) VALUES (
            :driver_id, :vehicle_id, :infraction_date, :infraction_type, :description,
            :location, :fine_amount, :points_deducted, :paid, :notes, :created_by
        )');

        $this->bind(':driver_id', $data['driver_id']);
        $this->bind(':vehicle_id', $data['vehicle_id'] ?? null);
        $this->bind(':infraction_date', $data['infraction_date']);
        $this->bind(':infraction_type', $data['infraction_type']);
        $this->bind(':description', $data['description'] ?? null);
        $this->bind(':location', $data['location'] ?? null);
        $this->bind(':fine_amount', $data['fine_amount'] ?? 0);
        $this->bind(':points_deducted', $data['points_deducted'] ?? 0);
        $this->bind(':paid', $data['paid'] ?? false);
        $this->bind(':notes', $data['notes'] ?? null);
        $this->bind(':created_by', $_SESSION['user_id']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    public function getExpiringLicenses($days = 30) {
        $this->query('SELECT dp.*, u.first_name, u.last_name, u.email, u.phone
            FROM driver_profiles dp
            LEFT JOIN users u ON dp.user_id = u.id
            WHERE dp.license_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY dp.license_expiry_date');
        $this->bind(':days', $days);
        return $this->fetchAll();
    }
}
