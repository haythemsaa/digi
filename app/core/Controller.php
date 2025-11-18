<?php
/**
 * Base Controller
 */

class Controller {

    /**
     * Load model
     */
    public function model($model) {
        require_once APP_PATH . '/models/' . $model . '.php';
        return new $model();
    }

    /**
     * Load view
     */
    public function view($view, $data = []) {
        extract($data);

        if (file_exists(APP_PATH . '/views/' . $view . '.php')) {
            require_once APP_PATH . '/views/' . $view . '.php';
        } else {
            die("View does not exist: " . $view);
        }
    }

    /**
     * JSON response for API
     */
    public function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Check if user has permission
     */
    public function checkPermission($permission) {
        if (!isset($_SESSION['role']) || !$this->hasPermission($_SESSION['role'], $permission)) {
            $this->jsonResponse(['error' => 'Permission denied'], 403);
        }
    }

    /**
     * Verify permissions based on role
     */
    private function hasPermission($role, $permission) {
        $permissions = [
            'admin' => ['*'],
            'manager' => ['fleet.*', 'maintenance.*', 'transport.*', 'reports.*'],
            'dispatcher' => ['fleet.view', 'transport.*', 'gps.*'],
            'driver' => ['fleet.view', 'maintenance.view', 'gps.view'],
            'mechanic' => ['maintenance.*', 'fleet.view']
        ];

        if (!isset($permissions[$role])) {
            return false;
        }

        // Admin has all permissions
        if (in_array('*', $permissions[$role])) {
            return true;
        }

        // Check specific permission
        foreach ($permissions[$role] as $allowed) {
            if ($allowed === $permission ||
                (strpos($allowed, '.*') !== false && strpos($permission, str_replace('.*', '', $allowed)) === 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect helper
     */
    public function redirect($path) {
        header('Location: ' . APP_URL . '/' . $path);
        exit();
    }

    /**
     * Validate input
     */
    public function validateInput($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $ruleSet = explode('|', $rule);

            foreach ($ruleSet as $r) {
                if ($r === 'required' && empty($data[$field])) {
                    $errors[$field] = ucfirst($field) . ' is required';
                }

                if (strpos($r, 'min:') === 0) {
                    $min = (int)str_replace('min:', '', $r);
                    if (strlen($data[$field]) < $min) {
                        $errors[$field] = ucfirst($field) . ' must be at least ' . $min . ' characters';
                    }
                }

                if (strpos($r, 'max:') === 0) {
                    $max = (int)str_replace('max:', '', $r);
                    if (strlen($data[$field]) > $max) {
                        $errors[$field] = ucfirst($field) . ' must not exceed ' . $max . ' characters';
                    }
                }

                if ($r === 'email' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Invalid email format';
                }
            }
        }

        return $errors;
    }
}
