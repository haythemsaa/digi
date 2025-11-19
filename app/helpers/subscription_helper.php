<?php

/**
 * Subscription Access Control Helper
 *
 * This helper provides global functions to check module access throughout the application
 */

/**
 * Check if current company has access to a specific module
 *
 * @param string $moduleCode Module code (gps, taxi, delivery, etc.)
 * @param int|null $companyId Company ID (defaults to session company_id)
 * @return bool
 */
function hasModuleAccess($moduleCode, $companyId = null) {
    // Get company ID from session if not provided
    if ($companyId === null) {
        $companyId = $_SESSION['company_id'] ?? null;
    }

    // If no company ID, deny access
    if (!$companyId) {
        return false;
    }

    // Create subscription model instance
    $db = new Database();
    $subscriptionModel = new SubscriptionManager();

    return $subscriptionModel->hasModuleAccess($companyId, $moduleCode);
}

/**
 * Get all active modules for current company
 *
 * @param int|null $companyId Company ID (defaults to session company_id)
 * @return array
 */
function getActiveModules($companyId = null) {
    if ($companyId === null) {
        $companyId = $_SESSION['company_id'] ?? null;
    }

    if (!$companyId) {
        return [];
    }

    $subscriptionModel = new SubscriptionManager();
    return $subscriptionModel->getCompanyActiveModules($companyId);
}

/**
 * Require module access - redirect if not authorized
 *
 * @param string $moduleCode Module code required
 * @param string|null $redirectUrl URL to redirect to if access denied (default: subscription page)
 */
function requireModule($moduleCode, $redirectUrl = null) {
    if (!hasModuleAccess($moduleCode)) {
        if ($redirectUrl === null) {
            $redirectUrl = APP_URL . '/subscription-manager';
        }

        flash('error', "Accès refusé. Vous devez souscrire au module pour accéder à cette fonctionnalité.");
        redirect($redirectUrl);
        exit;
    }
}

/**
 * Display upgrade message if module not available
 *
 * @param string $moduleCode Module code
 * @param string $moduleName Module display name
 * @return string HTML content
 */
function showUpgradeMessage($moduleCode, $moduleName = null) {
    if (hasModuleAccess($moduleCode)) {
        return '';
    }

    if ($moduleName === null) {
        $moduleName = $moduleCode;
    }

    $html = '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
    $html .= '<i class="fas fa-lock"></i> ';
    $html .= '<strong>Fonctionnalité Premium</strong> - ';
    $html .= 'Le module <strong>' . htmlspecialchars($moduleName) . '</strong> n\'est pas activé dans votre abonnement. ';
    $html .= '<a href="' . APP_URL . '/subscription-manager" class="alert-link">Souscrire maintenant</a>';
    $html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
    $html .= '<span aria-hidden="true">&times;</span>';
    $html .= '</button>';
    $html .= '</div>';

    return $html;
}

/**
 * Check if feature should be shown in menu
 *
 * @param string $moduleCode Module code
 * @return bool
 */
function showInMenu($moduleCode) {
    // For now, show all menu items but disable those without access
    // You can change this to hide items completely by returning hasModuleAccess($moduleCode)
    return true;
}

/**
 * Get menu item class based on module access
 *
 * @param string $moduleCode Module code
 * @return string CSS class
 */
function getMenuItemClass($moduleCode) {
    if (!hasModuleAccess($moduleCode)) {
        return 'disabled-menu-item'; // You can add this CSS class to gray out items
    }
    return '';
}

/**
 * Add lock icon to menu item if not accessible
 *
 * @param string $moduleCode Module code
 * @return string HTML for lock icon or empty string
 */
function getMenuLockIcon($moduleCode) {
    if (!hasModuleAccess($moduleCode)) {
        return ' <i class="fas fa-lock text-warning"></i>';
    }
    return '';
}

/**
 * Module access mapping
 * Maps controllers/features to required modules
 */
function getModuleMapping() {
    return [
        // GPS & Tracking
        'gps' => [
            'tracking',
            'geofencing',
            'maps',
            'location_history'
        ],

        // Taxi & VTC
        'taxi' => [
            'passenger_transport',
            'ride_bookings',
            'taxi_management'
        ],

        // Delivery AI
        'delivery' => [
            'smart_delivery',
            'route_optimization',
            'bin_packing'
        ],

        // Maintenance
        'maintenance' => [
            'preventive_maintenance',
            'interventions',
            'service_schedules'
        ],

        // Stocks
        'stocks' => [
            'inventory',
            'warehouse',
            'stock_movements'
        ],

        // HR
        'hr' => [
            'employees',
            'absences',
            'schedules',
            'payroll'
        ],

        // Fuel
        'fuel' => [
            'fuel_management',
            'fuel_cards',
            'consumption_tracking'
        ],

        // Missions
        'missions' => [
            'mission_planning',
            'mission_billing',
            'assignments'
        ]
    ];
}

/**
 * Get required module for a feature
 *
 * @param string $feature Feature/controller name
 * @return string|null Module code required or null if no restriction
 */
function getRequiredModule($feature) {
    $mapping = getModuleMapping();

    foreach ($mapping as $moduleCode => $features) {
        if (in_array($feature, $features)) {
            return $moduleCode;
        }
    }

    return null; // No module required - core feature
}

/**
 * Check feature access
 *
 * @param string $feature Feature/controller name
 * @param int|null $companyId Company ID
 * @return bool
 */
function hasFeatureAccess($feature, $companyId = null) {
    $requiredModule = getRequiredModule($feature);

    // If no module required, grant access
    if ($requiredModule === null) {
        return true;
    }

    return hasModuleAccess($requiredModule, $companyId);
}

/**
 * Get subscription status badge HTML
 *
 * @param string $status Status code
 * @return string HTML badge
 */
function getSubscriptionStatusBadge($status) {
    $statusConfig = [
        'active' => ['class' => 'success', 'text' => 'Actif', 'icon' => 'check-circle'],
        'suspended' => ['class' => 'warning', 'text' => 'Suspendu', 'icon' => 'pause-circle'],
        'cancelled' => ['class' => 'danger', 'text' => 'Annulé', 'icon' => 'times-circle'],
        'expired' => ['class' => 'secondary', 'text' => 'Expiré', 'icon' => 'clock']
    ];

    $config = $statusConfig[$status] ?? ['class' => 'secondary', 'text' => $status, 'icon' => 'question-circle'];

    return sprintf(
        '<span class="badge badge-%s"><i class="fas fa-%s"></i> %s</span>',
        $config['class'],
        $config['icon'],
        $config['text']
    );
}

/**
 * Format module price
 *
 * @param float $price Price amount
 * @param string $currency Currency code
 * @param string $period Billing period (monthly/yearly)
 * @return string Formatted price string
 */
function formatModulePrice($price, $currency = 'EUR', $period = 'monthly') {
    $symbol = $currency === 'EUR' ? '€' : $currency;
    $periodText = $period === 'monthly' ? '/mois' : '/an';

    return number_format($price, 2) . $symbol . $periodText;
}

/**
 * Calculate savings percentage
 *
 * @param float $originalPrice Original price
 * @param float $discountedPrice Discounted price
 * @return float Savings percentage
 */
function calculateSavings($originalPrice, $discountedPrice) {
    if ($originalPrice == 0) {
        return 0;
    }

    return round((($originalPrice - $discountedPrice) / $originalPrice) * 100, 2);
}

/**
 * Check if trial period is active
 *
 * @param string|null $trialEndsAt Trial end date
 * @return bool
 */
function isTrialActive($trialEndsAt) {
    if (!$trialEndsAt) {
        return false;
    }

    return strtotime($trialEndsAt) >= strtotime(date('Y-m-d'));
}

/**
 * Get days remaining in trial
 *
 * @param string|null $trialEndsAt Trial end date
 * @return int Days remaining (0 if expired or no trial)
 */
function getTrialDaysRemaining($trialEndsAt) {
    if (!$trialEndsAt) {
        return 0;
    }

    $days = floor((strtotime($trialEndsAt) - time()) / 86400);
    return max(0, $days);
}
