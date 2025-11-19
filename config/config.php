<?php
/**
 * Application Configuration
 */

// Application Settings
define('APP_NAME', 'Pakiparc Fleet Management');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost');
define('APP_ENV', 'development'); // development, production

// Security
define('ENCRYPTION_KEY', 'your-secret-key-change-this-in-production');
define('SESSION_LIFETIME', 7200); // 2 hours

// Timezone
date_default_timezone_set('Africa/Tunis');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// File Upload Settings
define('MAX_UPLOAD_SIZE', 10485760); // 10MB
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx']);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Email Configuration (for notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', 'noreply@pakiparc.local');
define('SMTP_FROM_NAME', 'Pakiparc System');

// SMS Configuration (for alerts)
define('SMS_API_KEY', '');
define('SMS_API_URL', '');

// Maps API (for GPS tracking)
define('MAPS_API_KEY', ''); // Google Maps or OpenStreetMap

// Load Helpers
require_once APP_PATH . '/helpers/subscription_helper.php';
require_once APP_PATH . '/helpers/multi_tenant_helper.php';
