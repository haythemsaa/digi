<?php
/**
 * User Model
 */

class User extends Database {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $this->query('SELECT * FROM users WHERE email = :email');
        $this->bind(':email', $email);
        return $this->fetch();
    }

    /**
     * Find user by username
     */
    public function findByUsername($username) {
        $this->query('SELECT * FROM users WHERE username = :username');
        $this->bind(':username', $username);
        return $this->fetch();
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        $this->query('SELECT * FROM users WHERE id = :id');
        $this->bind(':id', $id);
        return $this->fetch();
    }

    /**
     * Register new user
     */
    public function register($data) {
        $this->query('INSERT INTO users (username, email, password, first_name, last_name, phone, role)
                      VALUES (:username, :email, :password, :first_name, :last_name, :phone, :role)');

        $this->bind(':username', $data['username']);
        $this->bind(':email', $data['email']);
        $this->bind(':password', password_hash($data['password'], PASSWORD_BCRYPT));
        $this->bind(':first_name', $data['first_name']);
        $this->bind(':last_name', $data['last_name']);
        $this->bind(':phone', $data['phone'] ?? null);
        $this->bind(':role', $data['role'] ?? 'driver');

        if ($this->execute()) {
            return $this->lastInsertId();
        }

        return false;
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->query('UPDATE users SET last_login = NOW() WHERE id = :id');
            $this->bind(':id', $user['id']);
            $this->execute();

            return $user;
        }

        return false;
    }

    /**
     * Get all users
     */
    public function getAllUsers($limit = 50, $offset = 0) {
        $this->query('SELECT id, username, email, first_name, last_name, phone, role, status, last_login, created_at
                      FROM users
                      ORDER BY created_at DESC
                      LIMIT :limit OFFSET :offset');
        $this->bind(':limit', $limit);
        $this->bind(':offset', $offset);
        return $this->fetchAll();
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        $this->query('SELECT id, username, email, first_name, last_name, phone, role, status
                      FROM users
                      WHERE role = :role AND status = "active"
                      ORDER BY first_name, last_name');
        $this->bind(':role', $role);
        return $this->fetchAll();
    }

    /**
     * Update user
     */
    public function updateUser($id, $data) {
        $this->query('UPDATE users SET
                      first_name = :first_name,
                      last_name = :last_name,
                      phone = :phone,
                      role = :role,
                      status = :status
                      WHERE id = :id');

        $this->bind(':id', $id);
        $this->bind(':first_name', $data['first_name']);
        $this->bind(':last_name', $data['last_name']);
        $this->bind(':phone', $data['phone'] ?? null);
        $this->bind(':role', $data['role']);
        $this->bind(':status', $data['status']);

        return $this->execute();
    }

    /**
     * Update password
     */
    public function updatePassword($id, $newPassword) {
        $this->query('UPDATE users SET password = :password WHERE id = :id');
        $this->bind(':id', $id);
        $this->bind(':password', password_hash($newPassword, PASSWORD_BCRYPT));
        return $this->execute();
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        $this->query('DELETE FROM users WHERE id = :id');
        $this->bind(':id', $id);
        return $this->execute();
    }

    /**
     * Count total users
     */
    public function countUsers() {
        $this->query('SELECT COUNT(*) as total FROM users');
        $result = $this->fetch();
        return $result['total'];
    }

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
}
