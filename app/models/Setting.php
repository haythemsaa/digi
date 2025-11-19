<?php
/**
 * Settings Model - Multi-tenant enabled
 * Supports both global and company-specific settings
 */

class Setting {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();
    }

    /**
     * Get setting value
     * Checks company-specific first, then falls back to global
     */
    public function get($key, $default = null) {
        // Try company-specific setting first
        if ($this->companyId) {
            $this->db->query('SELECT setting_value FROM settings
                WHERE setting_key = :key AND company_id = :company_id');
            $this->db->bind(':key', $key);
            $this->db->bind(':company_id', $this->companyId);
            $result = $this->db->fetch();

            if ($result) {
                return $this->parseValue($result['setting_value']);
            }
        }

        // Fall back to global setting
        $this->db->query('SELECT setting_value FROM settings
            WHERE setting_key = :key AND company_id IS NULL');
        $this->db->bind(':key', $key);
        $result = $this->db->fetch();

        if ($result) {
            return $this->parseValue($result['setting_value']);
        }

        return $default;
    }

    /**
     * Set company setting
     */
    public function set($key, $value, $type = 'string', $description = null) {
        $companyId = $this->companyId;

        // Check if exists
        $this->db->query('SELECT id FROM settings
            WHERE setting_key = :key AND company_id = :company_id');
        $this->db->bind(':key', $key);
        $this->db->bind(':company_id', $companyId);
        $existing = $this->db->fetch();

        if ($existing) {
            // Update
            $this->db->query('UPDATE settings SET
                setting_value = :value,
                setting_type = :type,
                updated_by = :user_id
                WHERE setting_key = :key AND company_id = :company_id');

            $this->db->bind(':key', $key);
            $this->db->bind(':value', $this->stringifyValue($value));
            $this->db->bind(':type', $type);
            $this->db->bind(':company_id', $companyId);
            $this->db->bind(':user_id', $_SESSION['user_id'] ?? null);
        } else {
            // Insert
            $this->db->query('INSERT INTO settings
                (company_id, setting_key, setting_value, setting_type, description, updated_by)
                VALUES (:company_id, :key, :value, :type, :description, :user_id)');

            $this->db->bind(':company_id', $companyId);
            $this->db->bind(':key', $key);
            $this->db->bind(':value', $this->stringifyValue($value));
            $this->db->bind(':type', $type);
            $this->db->bind(':description', $description);
            $this->db->bind(':user_id', $_SESSION['user_id'] ?? null);
        }

        return $this->db->execute();
    }

    /**
     * Set global setting (super admin only)
     */
    public function setGlobal($key, $value, $type = 'string', $description = null) {
        if (!isSuperAdmin()) {
            return false;
        }

        // Check if exists
        $this->db->query('SELECT id FROM settings
            WHERE setting_key = :key AND company_id IS NULL');
        $this->db->bind(':key', $key);
        $existing = $this->db->fetch();

        if ($existing) {
            // Update
            $this->db->query('UPDATE settings SET
                setting_value = :value,
                setting_type = :type,
                updated_by = :user_id
                WHERE setting_key = :key AND company_id IS NULL');

            $this->db->bind(':key', $key);
            $this->db->bind(':value', $this->stringifyValue($value));
            $this->db->bind(':type', $type);
            $this->db->bind(':user_id', $_SESSION['user_id'] ?? null);
        } else {
            // Insert
            $this->db->query('INSERT INTO settings
                (company_id, setting_key, setting_value, setting_type, description, updated_by)
                VALUES (NULL, :key, :value, :type, :description, :user_id)');

            $this->db->bind(':key', $key);
            $this->db->bind(':value', $this->stringifyValue($value));
            $this->db->bind(':type', $type);
            $this->db->bind(':description', $description);
            $this->db->bind(':user_id', $_SESSION['user_id'] ?? null);
        }

        return $this->db->execute();
    }

    /**
     * Get all company settings
     */
    public function getAllSettings() {
        if (!$this->companyId) {
            return [];
        }

        $this->db->query('SELECT * FROM settings
            WHERE company_id = :company_id
            ORDER BY setting_key');
        $this->db->bind(':company_id', $this->companyId);

        return $this->db->fetchAll();
    }

    /**
     * Get all global settings (super admin only)
     */
    public function getAllGlobalSettings() {
        if (!isSuperAdmin()) {
            return [];
        }

        $this->db->query('SELECT * FROM settings
            WHERE company_id IS NULL
            ORDER BY setting_key');

        return $this->db->fetchAll();
    }

    /**
     * Get settings as array
     */
    public function getSettingsArray() {
        $settings = $this->getAllSettings();
        $array = [];

        foreach ($settings as $setting) {
            $array[$setting['setting_key']] = $this->parseValue($setting['setting_value']);
        }

        return $array;
    }

    /**
     * Delete setting
     */
    public function delete($key) {
        $this->db->query('DELETE FROM settings
            WHERE setting_key = :key AND company_id = :company_id');
        $this->db->bind(':key', $key);
        $this->db->bind(':company_id', $this->companyId);

        return $this->db->execute();
    }

    /**
     * Parse value based on type
     */
    private function parseValue($value) {
        if ($value === 'true') return true;
        if ($value === 'false') return false;
        if (is_numeric($value)) return $value + 0;
        if ($this->isJson($value)) return json_decode($value, true);
        return $value;
    }

    /**
     * Convert value to string
     */
    private function stringifyValue($value) {
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_array($value)) return json_encode($value);
        return (string)$value;
    }

    /**
     * Check if string is JSON
     */
    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
