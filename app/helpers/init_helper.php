<?php
/**
 * Initialization Helper
 * Loads all helper files and initializes core functions
 */

// Load all helper files
$helpers = [
    'subscription_helper.php',
    'multi_tenant_helper.php'
];

foreach ($helpers as $helper) {
    $helperPath = APP_PATH . '/helpers/' . $helper;
    if (file_exists($helperPath)) {
        require_once $helperPath;
    }
}

/**
 * Flash message helper
 */
function flash($name = '', $message = '', $class = 'alert-success') {
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'alert-success';

            // Determine icon and alert type
            $icons = [
                'alert-success' => 'check-circle',
                'alert-danger' => 'exclamation-triangle',
                'alert-warning' => 'exclamation-circle',
                'alert-info' => 'info-circle',
                'success' => 'check-circle',
                'error' => 'exclamation-triangle',
                'warning' => 'exclamation-circle',
                'info' => 'info-circle'
            ];

            // Map simple names to Bootstrap classes
            $alertClasses = [
                'success' => 'alert-success',
                'error' => 'alert-danger',
                'warning' => 'alert-warning',
                'info' => 'alert-info'
            ];

            $displayClass = $alertClasses[$class] ?? $class;
            $icon = $icons[$class] ?? 'info-circle';

            echo '<div class="alert ' . $displayClass . ' alert-dismissible fade show" role="alert">';
            echo '<i class="fas fa-' . $icon . '"></i> ';
            echo htmlspecialchars($_SESSION[$name]);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';

            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

/**
 * Redirect helper
 */
function redirect($page) {
    header('Location: ' . APP_URL . '/' . $page);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if user has role
 */
function hasRole($role) {
    return getUserRole() === $role;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return in_array(getUserRole(), ['admin', 'super_admin']);
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'TND') {
    return number_format($amount, 2, '.', ' ') . ' ' . $currency;
}

/**
 * Format date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Sanitize input
 */
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
        return $data;
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Debug helper
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

/**
 * Get APP_URL constant if not defined
 */
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost');
}

/**
 * Get APP_PATH constant if not defined
 */
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__));
}
