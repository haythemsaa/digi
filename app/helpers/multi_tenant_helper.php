<?php
/**
 * Multi-Tenant Helper Functions
 * Provides utilities for company isolation and data filtering
 */

/**
 * Get the current user's company ID from session
 * @return int|null Company ID or null if not set
 */
function getCurrentCompanyId() {
    if (isset($_SESSION['company_id'])) {
        return (int)$_SESSION['company_id'];
    }
    return null;
}

/**
 * Get current company data
 * @return array|null Company data or null if not set
 */
function getCurrentCompany() {
    if (isset($_SESSION['company_data'])) {
        return $_SESSION['company_data'];
    }
    return null;
}

/**
 * Check if current user is a super admin (platform admin)
 * @return bool
 */
function isSuperAdmin() {
    return isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;
}

/**
 * Require company context - redirect if not set
 * @param string $redirectTo Where to redirect if no company
 */
function requireCompany($redirectTo = 'login') {
    if (!getCurrentCompanyId() && !isSuperAdmin()) {
        flash('error', 'Session d\'entreprise invalide. Veuillez vous reconnecter.');
        redirect($redirectTo);
        exit;
    }
}

/**
 * Add company filter to WHERE clause
 * @param string $tableAlias Table alias (e.g., 'v', 'm')
 * @return string SQL WHERE condition for company_id
 */
function companyFilter($tableAlias = '') {
    $companyId = getCurrentCompanyId();

    // Super admins see all data
    if (isSuperAdmin()) {
        return '1=1'; // Always true condition
    }

    if (!$companyId) {
        return '1=0'; // Always false - no data visible
    }

    $prefix = $tableAlias ? $tableAlias . '.' : '';
    return "{$prefix}company_id = {$companyId}";
}

/**
 * Get company filter value for PDO binding
 * @return int Company ID to use in queries
 */
function getCompanyFilterValue() {
    return getCurrentCompanyId();
}

/**
 * Check if user can access data from a specific company
 * @param int $companyId Company ID to check
 * @return bool
 */
function canAccessCompany($companyId) {
    // Super admins can access all companies
    if (isSuperAdmin()) {
        return true;
    }

    // Regular users can only access their own company
    return getCurrentCompanyId() === (int)$companyId;
}

/**
 * Validate data belongs to current company
 * @param int $dataCompanyId Company ID of the data being accessed
 * @param string $errorMessage Custom error message
 * @return bool True if authorized, false otherwise
 */
function validateCompanyAccess($dataCompanyId, $errorMessage = 'Accès refusé à cette ressource') {
    if (!canAccessCompany($dataCompanyId)) {
        flash('error', $errorMessage);
        return false;
    }
    return true;
}

/**
 * Get company setting value
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value
 */
function getCompanySetting($key, $default = null) {
    $company = getCurrentCompany();
    return $company[$key] ?? $default;
}

/**
 * Format currency according to company settings
 * @param float $amount Amount to format
 * @param bool $showSymbol Whether to show currency symbol
 * @return string Formatted amount
 */
function formatCompanyCurrency($amount, $showSymbol = true) {
    $currency = getCompanySetting('currency', 'TND');
    $formatted = number_format($amount, 2, '.', ' ');

    if ($showSymbol) {
        return $formatted . ' ' . $currency;
    }

    return $formatted;
}

/**
 * Format date according to company settings
 * @param string $date Date string
 * @param bool $includeTime Whether to include time
 * @return string Formatted date
 */
function formatCompanyDate($date, $includeTime = false) {
    if (!$date) return '';

    $dateFormat = getCompanySetting('date_format', 'd/m/Y');
    $timeFormat = getCompanySetting('time_format', 'H:i');

    $format = $includeTime ? "{$dateFormat} {$timeFormat}" : $dateFormat;

    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Get company timezone
 * @return string Timezone identifier
 */
function getCompanyTimezone() {
    return getCompanySetting('timezone', 'Africa/Tunis');
}

/**
 * Check if company has reached vehicle limit
 * @param int $currentCount Current vehicle count (optional)
 * @return bool True if limit reached
 */
function hasReachedVehicleLimit($currentCount = null) {
    if (isSuperAdmin()) {
        return false; // No limits for super admins
    }

    $maxVehicles = getCompanySetting('max_vehicles', 10);

    if ($currentCount === null) {
        // Count vehicles if not provided
        $db = new Database();
        $db->query("SELECT COUNT(*) as count FROM vehicles WHERE company_id = :company_id");
        $db->bind(':company_id', getCurrentCompanyId());
        $result = $db->fetch();
        $currentCount = $result['count'];
    }

    return $currentCount >= $maxVehicles;
}

/**
 * Check if company has reached driver limit
 * @param int $currentCount Current driver count (optional)
 * @return bool True if limit reached
 */
function hasReachedDriverLimit($currentCount = null) {
    if (isSuperAdmin()) {
        return false;
    }

    $maxDrivers = getCompanySetting('max_drivers', 10);

    if ($currentCount === null) {
        $db = new Database();
        $db->query("SELECT COUNT(*) as count FROM drivers WHERE company_id = :company_id");
        $db->bind(':company_id', getCurrentCompanyId());
        $result = $db->fetch();
        $currentCount = $result['count'];
    }

    return $currentCount >= $maxDrivers;
}

/**
 * Check if company has reached user limit
 * @param int $currentCount Current user count (optional)
 * @return bool True if limit reached
 */
function hasReachedUserLimit($currentCount = null) {
    if (isSuperAdmin()) {
        return false;
    }

    $maxUsers = getCompanySetting('max_users', 5);

    if ($currentCount === null) {
        $db = new Database();
        $db->query("SELECT COUNT(*) as count FROM users WHERE company_id = :company_id");
        $db->bind(':company_id', getCurrentCompanyId());
        $result = $db->fetch();
        $currentCount = $result['count'];
    }

    return $currentCount >= $maxUsers;
}

/**
 * Get company subscription status
 * @return string Subscription status (trial, active, suspended, cancelled, expired)
 */
function getCompanySubscriptionStatus() {
    return getCompanySetting('subscription_status', 'trial');
}

/**
 * Check if company subscription is active
 * @return bool True if active or in trial
 */
function isCompanySubscriptionActive() {
    $status = getCompanySubscriptionStatus();
    return in_array($status, ['trial', 'active']);
}

/**
 * Check if company is in trial period
 * @return bool True if in trial
 */
function isCompanyInTrial() {
    return getCompanySubscriptionStatus() === 'trial';
}

/**
 * Get days remaining in trial
 * @return int Days remaining (0 if expired or not in trial)
 */
function getTrialDaysRemaining() {
    if (!isCompanyInTrial()) {
        return 0;
    }

    $trialEndsAt = getCompanySetting('trial_ends_at');
    if (!$trialEndsAt) {
        return 0;
    }

    $now = new DateTime();
    $end = new DateTime($trialEndsAt);
    $diff = $now->diff($end);

    return $diff->invert ? 0 : $diff->days;
}

/**
 * Load company data into session
 * @param int $companyId Company ID
 * @return bool Success
 */
function loadCompanyIntoSession($companyId) {
    $db = new Database();
    $db->query("SELECT * FROM companies WHERE id = :id AND status = 'active'");
    $db->bind(':id', $companyId);
    $company = $db->fetch();

    if ($company) {
        $_SESSION['company_id'] = $company['id'];
        $_SESSION['company_data'] = $company;
        return true;
    }

    return false;
}

/**
 * Clear company data from session
 */
function clearCompanyFromSession() {
    unset($_SESSION['company_id']);
    unset($_SESSION['company_data']);
}

/**
 * Log company activity
 * @param string $action Action performed
 * @param string $description Description of action
 * @param array $metadata Additional metadata (will be stored as JSON)
 */
function logCompanyActivity($action, $description, $metadata = []) {
    $db = new Database();

    $db->query("INSERT INTO company_activity_log
        (company_id, user_id, action, description, metadata, ip_address, user_agent)
        VALUES (:company_id, :user_id, :action, :description, :metadata, :ip_address, :user_agent)");

    $db->bind(':company_id', getCurrentCompanyId());
    $db->bind(':user_id', $_SESSION['user_id'] ?? null);
    $db->bind(':action', $action);
    $db->bind(':description', $description);
    $db->bind(':metadata', json_encode($metadata));
    $db->bind(':ip_address', $_SERVER['REMOTE_ADDR'] ?? null);
    $db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? null);

    $db->execute();
}

/**
 * Get company name
 * @return string Company name
 */
function getCompanyName() {
    return getCompanySetting('company_name', 'DigiParc');
}

/**
 * Get company logo URL
 * @return string|null Logo URL or null
 */
function getCompanyLogo() {
    $logo = getCompanySetting('logo');
    if ($logo) {
        return APP_URL . '/public/uploads/company_logos/' . $logo;
    }
    return null;
}

/**
 * Get company primary color
 * @return string Hex color code
 */
function getCompanyPrimaryColor() {
    return getCompanySetting('primary_color', '#007bff');
}

/**
 * Check if company has module access
 * @param string $moduleCode Module code
 * @return bool True if has access
 */
function companyHasModule($moduleCode) {
    // This integrates with existing hasModuleAccess function
    return hasModuleAccess($moduleCode);
}
