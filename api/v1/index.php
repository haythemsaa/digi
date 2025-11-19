<?php

/**
 * Pakiparc REST API v1
 * Main entry point for API requests
 *
 * @author Pakiparc Team
 * @version 1.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load configuration and autoloader
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RateLimitMiddleware.php';

// Initialize router
$router = new APIRouter();

try {
    // Authentication endpoints
    $router->post('/auth/login', 'AuthAPI@login');
    $router->post('/auth/logout', 'AuthAPI@logout');
    $router->post('/auth/refresh', 'AuthAPI@refresh');

    // Vehicles endpoints (require auth)
    $router->get('/vehicles', 'VehiclesAPI@index', ['auth']);
    $router->get('/vehicles/{id}', 'VehiclesAPI@show', ['auth']);
    $router->post('/vehicles', 'VehiclesAPI@create', ['auth']);
    $router->put('/vehicles/{id}', 'VehiclesAPI@update', ['auth']);
    $router->delete('/vehicles/{id}', 'VehiclesAPI@delete', ['auth']);

    // Drivers endpoints
    $router->get('/drivers', 'DriversAPI@index', ['auth']);
    $router->get('/drivers/{id}', 'DriversAPI@show', ['auth']);
    $router->post('/drivers', 'DriversAPI@create', ['auth']);
    $router->put('/drivers/{id}', 'DriversAPI@update', ['auth']);
    $router->delete('/drivers/{id}', 'DriversAPI@delete', ['auth']);

    // Missions endpoints
    $router->get('/missions', 'MissionsAPI@index', ['auth']);
    $router->get('/missions/{id}', 'MissionsAPI@show', ['auth']);
    $router->post('/missions', 'MissionsAPI@create', ['auth']);
    $router->put('/missions/{id}', 'MissionsAPI@update', ['auth']);
    $router->put('/missions/{id}/status', 'MissionsAPI@updateStatus', ['auth']);

    // Tracking endpoints
    $router->get('/tracking/live', 'TrackingAPI@live', ['auth']);
    $router->post('/tracking/position', 'TrackingAPI@recordPosition', ['auth']);
    $router->get('/tracking/history/{vehicleId}', 'TrackingAPI@history', ['auth']);

    // Analytics endpoints
    $router->get('/analytics/dashboard', 'AnalyticsAPI@dashboard', ['auth']);
    $router->get('/analytics/kpis', 'AnalyticsAPI@kpis', ['auth']);
    $router->get('/analytics/trends', 'AnalyticsAPI@trends', ['auth']);

    // Alerts endpoints
    $router->get('/alerts', 'AlertsAPI@index', ['auth']);
    $router->get('/alerts/{id}', 'AlertsAPI@show', ['auth']);
    $router->put('/alerts/{id}/acknowledge', 'AlertsAPI@acknowledge', ['auth']);

    // Carbon endpoints
    $router->get('/carbon/footprint', 'CarbonAPI@footprint', ['auth']);
    $router->get('/carbon/recommendations', 'CarbonAPI@recommendations', ['auth']);

    // Dispatch request
    $router->dispatch();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Internal server error',
        'details' => DEBUG ? $e->getMessage() : null
    ]);
}
