<?php
/**
 * DigiParc - Fleet Management System
 * Entry Point
 */

session_start();

// Define constants
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load configuration
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/database.php';

// Initialize application
require_once APP_PATH . '/core/App.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Database.php';

// Start the application
$app = new App();
