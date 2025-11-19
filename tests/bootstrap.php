<?php

/**
 * PHPUnit Bootstrap File
 * Sets up test environment
 */

// Define test environment
define('TESTING', true);

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Load core classes
require_once __DIR__ . '/../api/core/Database.php';

// Load helpers
require_once __DIR__ . '/../app/helpers/init_helper.php';
require_once __DIR__ . '/../app/helpers/multi_tenant_helper.php';
require_once __DIR__ . '/../app/helpers/i18n_helper.php';

// Start session for tests
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set up test database connection
function getTestDatabase()
{
    static $db = null;

    if ($db === null) {
        $db = Database::getInstance()->getConnection();
    }

    return $db;
}

// Helper function to create test company
function createTestCompany($name = 'Test Company')
{
    $db = getTestDatabase();

    $sql = "INSERT INTO companies (name, legal_name, subscription_plan, status, created_at)
            VALUES (:name, :legal_name, 'professional', 'active', NOW())";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':name', $name);
    $legalName = $name . ' Ltd.';
    $stmt->bindParam(':legal_name', $legalName);
    $stmt->execute();

    return $db->lastInsertId();
}

// Helper function to clean up test data
function cleanupTestData()
{
    $db = getTestDatabase();

    // Clean up in reverse order of dependencies
    $tables = [
        'alerts',
        'carbon_tracking',
        'tracking',
        'missions',
        'maintenance',
        'fuel_transactions',
        'drivers',
        'vehicles',
        'users',
        'companies'
    ];

    foreach ($tables as $table) {
        $sql = "DELETE FROM $table WHERE name LIKE 'Test%' OR email LIKE 'test%'";
        try {
            $db->exec($sql);
        } catch (Exception $e) {
            // Table might not exist, continue
        }
    }
}

// Register cleanup on shutdown
register_shutdown_function('cleanupTestData');
