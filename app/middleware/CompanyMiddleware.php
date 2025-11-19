<?php
/**
 * Company Middleware
 * Ensures company context is loaded and valid for multi-tenant operations
 */

class CompanyMiddleware {

    /**
     * Verify company context exists
     * Called at the beginning of protected pages
     */
    public static function requireCompanyContext($redirectTo = 'login') {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            flash('error', 'Veuillez vous connecter pour accéder à cette page');
            redirect($redirectTo);
            exit;
        }

        // Super admins can bypass company requirement in some contexts
        if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true) {
            return true;
        }

        // Verify company context exists
        if (!isset($_SESSION['company_id']) || empty($_SESSION['company_id'])) {
            flash('error', 'Contexte d\'entreprise invalide. Veuillez vous reconnecter.');
            redirect($redirectTo);
            exit;
        }

        // Verify company is still active
        $db = new Database();
        $db->query("SELECT status FROM companies WHERE id = :company_id");
        $db->bind(':company_id', $_SESSION['company_id']);
        $company = $db->fetch();

        if (!$company || $company['status'] !== 'active') {
            // Company is no longer active
            flash('error', 'Votre entreprise n\'est plus active. Contactez l\'administrateur.');

            // Clear session
            clearCompanyFromSession();
            unset($_SESSION['user_id']);
            session_destroy();

            redirect($redirectTo);
            exit;
        }

        return true;
    }

    /**
     * Require super admin privileges
     */
    public static function requireSuperAdmin($redirectTo = 'dashboard') {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            flash('error', 'Veuillez vous connecter');
            redirect('login');
            exit;
        }

        if (!isset($_SESSION['is_super_admin']) || $_SESSION['is_super_admin'] !== true) {
            flash('error', 'Accès refusé. Privilèges administrateur requis.');
            redirect($redirectTo);
            exit;
        }

        return true;
    }

    /**
     * Check subscription status and module access
     */
    public static function checkSubscription() {
        // Skip for super admins
        if (isSuperAdmin()) {
            return true;
        }

        $company = getCurrentCompany();

        if (!$company) {
            return false;
        }

        $status = $company['subscription_status'] ?? 'trial';

        // Check if subscription is active
        if (!in_array($status, ['trial', 'active'])) {
            flash('error', 'Votre abonnement n\'est pas actif. Veuillez renouveler votre abonnement.');
            redirect('subscription/expired');
            exit;
        }

        // Check if trial has expired
        if ($status === 'trial' && isset($company['trial_ends_at'])) {
            $trialEnd = new DateTime($company['trial_ends_at']);
            $now = new DateTime();

            if ($now > $trialEnd) {
                flash('error', 'Votre période d\'essai a expiré. Veuillez souscrire à un abonnement.');
                redirect('subscription/expired');
                exit;
            }
        }

        return true;
    }

    /**
     * Check if company has access to specific module
     */
    public static function requireModule($moduleCode, $redirectTo = 'dashboard') {
        // Skip for super admins
        if (isSuperAdmin()) {
            return true;
        }

        if (!hasModuleAccess($moduleCode)) {
            flash('error', 'Votre abonnement ne vous donne pas accès à ce module.');
            redirect($redirectTo);
            exit;
        }

        return true;
    }

    /**
     * Check resource limits (vehicles, drivers, users)
     */
    public static function checkResourceLimit($resourceType) {
        // Skip for super admins
        if (isSuperAdmin()) {
            return true;
        }

        $hasReached = false;

        switch ($resourceType) {
            case 'vehicles':
                $hasReached = hasReachedVehicleLimit();
                break;
            case 'drivers':
                $hasReached = hasReachedDriverLimit();
                break;
            case 'users':
                $hasReached = hasReachedUserLimit();
                break;
        }

        if ($hasReached) {
            flash('error', "Vous avez atteint la limite de {$resourceType} pour votre abonnement. Veuillez upgrader votre plan.");
            return false;
        }

        return true;
    }

    /**
     * Log company activity
     */
    public static function logActivity($action, $description, $metadata = []) {
        $companyId = getCurrentCompanyId();

        if (!$companyId) {
            return;
        }

        logCompanyActivity($action, $description, $metadata);
    }

    /**
     * Validate data belongs to company
     * Prevents cross-company data access
     */
    public static function validateDataAccess($dataCompanyId, $errorMessage = 'Accès refusé à cette ressource') {
        if (!canAccessCompany($dataCompanyId)) {
            flash('error', $errorMessage);
            redirect('dashboard');
            exit;
        }

        return true;
    }

    /**
     * Get company limits summary
     */
    public static function getCompanyLimits() {
        if (isSuperAdmin()) {
            return [
                'vehicles' => ['current' => 0, 'max' => 9999, 'reached' => false],
                'drivers' => ['current' => 0, 'max' => 9999, 'reached' => false],
                'users' => ['current' => 0, 'max' => 9999, 'reached' => false]
            ];
        }

        $company = getCurrentCompany();
        $db = new Database();
        $companyId = getCurrentCompanyId();

        // Count vehicles
        $db->query("SELECT COUNT(*) as count FROM vehicles WHERE company_id = :company_id");
        $db->bind(':company_id', $companyId);
        $vehicleCount = $db->fetch()['count'];

        // Count drivers
        $db->query("SELECT COUNT(*) as count FROM drivers WHERE company_id = :company_id");
        $db->bind(':company_id', $companyId);
        $driverCount = $db->fetch()['count'];

        // Count users
        $db->query("SELECT COUNT(*) as count FROM users WHERE company_id = :company_id");
        $db->bind(':company_id', $companyId);
        $userCount = $db->fetch()['count'];

        return [
            'vehicles' => [
                'current' => $vehicleCount,
                'max' => $company['max_vehicles'] ?? 10,
                'reached' => $vehicleCount >= ($company['max_vehicles'] ?? 10)
            ],
            'drivers' => [
                'current' => $driverCount,
                'max' => $company['max_drivers'] ?? 10,
                'reached' => $driverCount >= ($company['max_drivers'] ?? 10)
            ],
            'users' => [
                'current' => $userCount,
                'max' => $company['max_users'] ?? 5,
                'reached' => $userCount >= ($company['max_users'] ?? 5)
            ]
        ];
    }
}
