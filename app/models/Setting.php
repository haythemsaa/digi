<?php
/**
 * Settings Model
 */

class Setting extends Database {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all settings
     */
    public function getAllSettings() {
        $this->query('SELECT * FROM settings ORDER BY setting_key');
        return $this->fetchAll();
    }

    /**
     * Get setting by key
     */
    public function get($key, $default = null) {
        $this->query('SELECT setting_value FROM settings WHERE setting_key = :key');
        $this->bind(':key', $key);
        $result = $this->fetch();

        if ($result) {
            return $result['setting_value'];
        }

        return $default;
    }

    /**
     * Set setting
     */
    public function set($key, $value, $type = 'string', $description = null) {
        // Check if exists
        $this->query('SELECT id FROM settings WHERE setting_key = :key');
        $this->bind(':key', $key);
        $existing = $this->fetch();

        if ($existing) {
            // Update
            $this->query('UPDATE settings SET setting_value = :value, setting_type = :type, updated_by = :user_id WHERE setting_key = :key');
            $this->bind(':key', $key);
            $this->bind(':value', $value);
            $this->bind(':type', $type);
            $this->bind(':user_id', $_SESSION['user_id'] ?? null);
        } else {
            // Insert
            $this->query('INSERT INTO settings (setting_key, setting_value, setting_type, description, updated_by)
                          VALUES (:key, :value, :type, :description, :user_id)');
            $this->bind(':key', $key);
            $this->bind(':value', $value);
            $this->bind(':type', $type);
            $this->bind(':description', $description);
            $this->bind(':user_id', $_SESSION['user_id'] ?? null);
        }

        return $this->execute();
    }

    /**
     * Delete setting
     */
    public function delete($key) {
        $this->query('DELETE FROM settings WHERE setting_key = :key');
        $this->bind(':key', $key);
        return $this->execute();
    }

    /**
     * Get settings as array
     */
    public function getSettingsArray() {
        $settings = $this->getAllSettings();
        $array = [];

        foreach ($settings as $setting) {
            $array[$setting['setting_key']] = $setting['setting_value'];
        }

        return $array;
    }
}
