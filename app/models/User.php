<?php
/**
 * User Model - Multi-tenant enabled
 */

class User {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();

        // Company context is not required for authentication methods
        // but is required for user management operations
    }

    /**
     * Get company filter for SQL queries
     */
    private function getCompanyFilter($tableAlias = 'u') {
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

    // ========================================
    // AUTHENTICATION METHODS
    // ========================================

    /**
     * Find user by email (for authentication)
     * Note: No company filter here as email is unique across platform
     */
    public function findByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        return $this->db->fetch();
    }

    /**
     * Find user by username
     */
    public function findByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        return $this->db->fetch();
    }

    /**
     * Find user by ID (with optional company filter)
     */
    public function findById($id, $requireCompanyMatch = true) {
        if ($requireCompanyMatch && $this->companyId) {
            $filter = $this->getCompanyFilter('u');
            $this->db->query("SELECT * FROM users u WHERE u.id = :id AND {$filter}");
            $this->db->bind(':id', $id);
            $this->bindCompanyId();
        } else {
            $this->db->query('SELECT * FROM users WHERE id = :id');
            $this->db->bind(':id', $id);
        }
        return $this->db->fetch();
    }

    /**
     * Login user - verify credentials and return user data
     */
    public function login($email, $password) {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Check if user's company is active
            if ($user['company_id']) {
                $this->db->query("SELECT status FROM companies WHERE id = :company_id");
                $this->db->bind(':company_id', $user['company_id']);
                $company = $this->db->fetch();

                if (!$company || $company['status'] !== 'active') {
                    return false; // Company is not active
                }
            }

            // Update last login
            $this->db->query('UPDATE users SET last_login = NOW() WHERE id = :id');
            $this->db->bind(':id', $user['id']);
            $this->db->execute();

            return $user;
        }

        return false;
    }

    // ========================================
    // USER MANAGEMENT (COMPANY-SCOPED)
    // ========================================

    /**
     * Register new user
     */
    public function register($data) {
        // Check user limit for company
        if (!isSuperAdmin() && hasReachedUserLimit()) {
            return false;
        }

        // Use provided company_id or current company
        $companyId = $data['company_id'] ?? $this->companyId;

        if (!$companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }

        $this->db->query('INSERT INTO users (
            company_id, username, email, password, first_name, last_name, phone, role, is_super_admin
        ) VALUES (
            :company_id, :username, :email, :password, :first_name, :last_name, :phone, :role, :is_super_admin
        )');

        $this->db->bind(':company_id', $companyId);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':role', $data['role'] ?? 'user');
        $this->db->bind(':is_super_admin', $data['is_super_admin'] ?? 0);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get all users (company-scoped)
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT u.id, u.company_id, u.username, u.email, u.first_name, u.last_name,
                         u.phone, u.role, u.status, u.is_super_admin, u.last_login, u.created_at
                      FROM users u
                      WHERE {$filter}
                      ORDER BY u.created_at DESC
                      LIMIT :limit OFFSET :offset");

        $this->bindCompanyId();
        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);
        return $this->db->fetchAll();
    }

    /**
     * Get users by role (company-scoped)
     */
    public function getUsersByRole($role) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.phone, u.role, u.status
                      FROM users u
                      WHERE u.role = :role AND u.status = 'active' AND {$filter}
                      ORDER BY u.first_name, u.last_name");

        $this->db->bind(':role', $role);
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Get users by status
     */
    public function getUsersByStatus($status) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT u.* FROM users u
                      WHERE u.status = :status AND {$filter}
                      ORDER BY u.first_name, u.last_name");

        $this->db->bind(':status', $status);
        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Search users
     */
    public function searchUsers($keyword) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT u.* FROM users u
                      WHERE {$filter}
                      AND (u.first_name LIKE :keyword
                         OR u.last_name LIKE :keyword
                         OR u.email LIKE :keyword
                         OR u.username LIKE :keyword)
                      ORDER BY u.first_name, u.last_name");

        $this->bindCompanyId();
        $this->db->bind(':keyword', '%' . $keyword . '%');
        return $this->db->fetchAll();
    }

    /**
     * Update user
     */
    public function updateUser($id, $data) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("UPDATE users u SET
                      u.first_name = :first_name,
                      u.last_name = :last_name,
                      u.phone = :phone,
                      u.role = :role,
                      u.status = :status
                      WHERE u.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':status', $data['status']);

        return $this->db->execute();
    }

    /**
     * Update user profile (self-service)
     */
    public function updateProfile($id, $data) {
        // No company filter for profile updates - user can only update their own
        $this->db->query("UPDATE users SET
                      first_name = :first_name,
                      last_name = :last_name,
                      phone = :phone,
                      email = :email
                      WHERE id = :id");

        $this->db->bind(':id', $id);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':email', $data['email']);

        return $this->db->execute();
    }

    /**
     * Update password
     */
    public function updatePassword($id, $newPassword) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("UPDATE users u SET u.password = :password
                      WHERE u.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        $this->db->bind(':password', password_hash($newPassword, PASSWORD_BCRYPT));

        return $this->db->execute();
    }

    /**
     * Change own password (no company filter needed)
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        // Verify old password first
        $user = $this->findById($id, false);

        if (!$user || !password_verify($oldPassword, $user['password'])) {
            return false;
        }

        $this->db->query("UPDATE users SET password = :password WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':password', password_hash($newPassword, PASSWORD_BCRYPT));

        return $this->db->execute();
    }

    /**
     * Delete user (soft delete - set status to inactive)
     */
    public function deleteUser($id) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("UPDATE users u SET u.status = 'inactive'
                      WHERE u.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();

        return $this->db->execute();
    }

    /**
     * Permanently delete user (use with caution)
     */
    public function permanentlyDeleteUser($id) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("DELETE FROM users WHERE id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();

        return $this->db->execute();
    }

    /**
     * Count total users (company-scoped)
     */
    public function countUsers() {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT COUNT(*) as total FROM users u WHERE {$filter}");
        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $result['total'];
    }

    /**
     * Count users by role
     */
    public function countUsersByRole($role) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT COUNT(*) as total FROM users u
                      WHERE u.role = :role AND {$filter}");

        $this->db->bind(':role', $role);
        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $result['total'];
    }

    /**
     * Count users by status
     */
    public function countUsersByStatus($status) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT COUNT(*) as total FROM users u
                      WHERE u.status = :status AND {$filter}");

        $this->db->bind(':status', $status);
        $this->bindCompanyId();
        $result = $this->db->fetch();

        return $result['total'];
    }

    // ========================================
    // ROLE-SPECIFIC METHODS
    // ========================================

    /**
     * Get drivers (for assignments)
     */
    public function getDrivers() {
        return $this->getUsersByRole('driver');
    }

    /**
     * Get mechanics (for work orders)
     */
    public function getMechanics() {
        return $this->getUsersByRole('mechanic');
    }

    /**
     * Get admins
     */
    public function getAdmins() {
        return $this->getUsersByRole('admin');
    }

    /**
     * Get managers
     */
    public function getManagers() {
        return $this->getUsersByRole('manager');
    }

    // ========================================
    // DASHBOARD & STATS
    // ========================================

    /**
     * Get dashboard stats
     */
    public function getDashboardStats() {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT
            COUNT(*) as total_users,
            SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as active_users,
            SUM(CASE WHEN u.status = 'inactive' THEN 1 ELSE 0 END) as inactive_users,
            SUM(CASE WHEN u.role = 'admin' THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN u.role = 'driver' THEN 1 ELSE 0 END) as drivers,
            SUM(CASE WHEN u.last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as active_last_week
            FROM users u WHERE {$filter}");

        $this->bindCompanyId();
        return $this->db->fetch();
    }

    /**
     * Get recent users
     */
    public function getRecentUsers($limit = 10) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT u.id, u.username, u.email, u.first_name, u.last_name,
                         u.role, u.status, u.created_at
                      FROM users u
                      WHERE {$filter}
                      ORDER BY u.created_at DESC
                      LIMIT :limit");

        $this->bindCompanyId();
        $this->db->bind(':limit', $limit);

        return $this->db->fetchAll();
    }

    /**
     * Get user activity (last login times)
     */
    public function getUserActivity($days = 30) {
        $filter = $this->getCompanyFilter('u');

        $this->db->query("SELECT u.id, u.username, u.first_name, u.last_name,
                         u.role, u.last_login,
                         DATEDIFF(NOW(), u.last_login) as days_since_login
                      FROM users u
                      WHERE {$filter}
                      AND u.status = 'active'
                      AND u.last_login IS NOT NULL
                      ORDER BY u.last_login DESC");

        $this->bindCompanyId();

        return $this->db->fetchAll();
    }

    // ========================================
    // SUPER ADMIN METHODS
    // ========================================

    /**
     * Get all users across all companies (super admin only)
     */
    public function getAllUsersAllCompanies($limit = 100, $offset = 0) {
        if (!isSuperAdmin()) {
            return [];
        }

        $this->db->query("SELECT u.*, c.company_name
                      FROM users u
                      LEFT JOIN companies c ON u.company_id = c.id
                      ORDER BY u.created_at DESC
                      LIMIT :limit OFFSET :offset");

        $this->db->bind(':limit', $limit);
        $this->db->bind(':offset', $offset);

        return $this->db->fetchAll();
    }

    /**
     * Get users for a specific company (super admin only)
     */
    public function getUsersByCompany($companyId) {
        if (!isSuperAdmin()) {
            return [];
        }

        $this->db->query("SELECT * FROM users
                      WHERE company_id = :company_id
                      ORDER BY first_name, last_name");

        $this->db->bind(':company_id', $companyId);

        return $this->db->fetchAll();
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeUserId = null) {
        if ($excludeUserId) {
            $this->db->query("SELECT COUNT(*) as count FROM users
                          WHERE email = :email AND id != :exclude_id");
            $this->db->bind(':email', $email);
            $this->db->bind(':exclude_id', $excludeUserId);
        } else {
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE email = :email");
            $this->db->bind(':email', $email);
        }

        $result = $this->db->fetch();
        return $result['count'] > 0;
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeUserId = null) {
        if ($excludeUserId) {
            $this->db->query("SELECT COUNT(*) as count FROM users
                          WHERE username = :username AND id != :exclude_id");
            $this->db->bind(':username', $username);
            $this->db->bind(':exclude_id', $excludeUserId);
        } else {
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE username = :username");
            $this->db->bind(':username', $username);
        }

        $result = $this->db->fetch();
        return $result['count'] > 0;
    }
}
