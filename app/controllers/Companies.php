<?php
/**
 * Companies Controller - Super Admin Only
 * Manages all companies in the multi-tenant system
 */

class Companies extends Controller {

    private $companyModel;
    private $userModel;

    public function __construct() {
        // Require super admin access for this entire controller
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            exit;
        }

        if (!isSuperAdmin()) {
            flash('error', 'Accès refusé. Administrateur système requis.');
            $this->redirect('dashboard');
            exit;
        }

        $this->companyModel = $this->model('Company');
        $this->userModel = $this->model('User');
    }

    /**
     * List all companies
     */
    public function index() {
        $companies = $this->companyModel->getCompaniesWithStats();

        $data = [
            'title' => 'Gestion des Entreprises',
            'companies' => $companies
        ];

        $this->view('companies/index', $data);
    }

    /**
     * View company details
     */
    public function view($id) {
        $company = $this->companyModel->getCompanyById($id);

        if (!$company) {
            flash('error', 'Entreprise introuvable');
            $this->redirect('companies');
        }

        // Get company stats
        $stats = $this->companyModel->getCompanyStats($id);

        // Get company users
        $users = $this->userModel->getUsersByCompany($id);

        $data = [
            'title' => 'Détails Entreprise - ' . $company['company_name'],
            'company' => $company,
            'stats' => $stats,
            'users' => $users
        ];

        $this->view('companies/view', $data);
    }

    /**
     * Create new company
     */
    public function create() {
        $data = [
            'title' => 'Nouvelle Entreprise',
            'company_name' => '',
            'legal_name' => '',
            'company_code' => '',
            'business_type' => '',
            'registration_number' => '',
            'tax_id' => '',
            'email' => '',
            'phone' => '',
            'website' => '',
            'address' => '',
            'city' => '',
            'postal_code' => '',
            'country' => 'Tunisia',
            'max_users' => 5,
            'max_vehicles' => 10,
            'max_drivers' => 10,
            'subscription_plan' => 'starter',
            'trial_days' => 30,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Populate data array
            $data['company_name'] = trim($_POST['company_name']);
            $data['legal_name'] = trim($_POST['legal_name']);
            $data['company_code'] = trim($_POST['company_code']);
            $data['business_type'] = trim($_POST['business_type'] ?? '');
            $data['registration_number'] = trim($_POST['registration_number'] ?? '');
            $data['tax_id'] = trim($_POST['tax_id'] ?? '');
            $data['email'] = trim($_POST['email']);
            $data['phone'] = trim($_POST['phone'] ?? '');
            $data['website'] = trim($_POST['website'] ?? '');
            $data['address'] = trim($_POST['address'] ?? '');
            $data['city'] = trim($_POST['city'] ?? '');
            $data['postal_code'] = trim($_POST['postal_code'] ?? '');
            $data['country'] = trim($_POST['country'] ?? 'Tunisia');
            $data['max_users'] = intval($_POST['max_users'] ?? 5);
            $data['max_vehicles'] = intval($_POST['max_vehicles'] ?? 10);
            $data['max_drivers'] = intval($_POST['max_drivers'] ?? 10);
            $data['subscription_plan'] = trim($_POST['subscription_plan'] ?? 'starter');
            $data['trial_days'] = intval($_POST['trial_days'] ?? 30);

            // Validate
            if (empty($data['company_name'])) {
                $data['errors']['company_name'] = 'Nom de l\'entreprise requis';
            }

            if (empty($data['email'])) {
                $data['errors']['email'] = 'Email requis';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['errors']['email'] = 'Email invalide';
            }

            // Check if company code already exists
            if (!empty($data['company_code']) && $this->companyModel->companyCodeExists($data['company_code'])) {
                $data['errors']['company_code'] = 'Ce code entreprise existe déjà';
            }

            // If no errors, create company
            if (empty($data['errors'])) {
                $companyId = $this->companyModel->createCompany($data);

                if ($companyId) {
                    flash('success', 'Entreprise créée avec succès');
                    $this->redirect('companies/view/' . $companyId);
                } else {
                    flash('error', 'Erreur lors de la création de l\'entreprise');
                }
            }
        }

        $this->view('companies/create', $data);
    }

    /**
     * Edit company
     */
    public function edit($id) {
        $company = $this->companyModel->getCompanyById($id);

        if (!$company) {
            flash('error', 'Entreprise introuvable');
            $this->redirect('companies');
        }

        $data = [
            'title' => 'Modifier Entreprise - ' . $company['company_name'],
            'company' => $company,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            // Build update data array
            $updateData = [
                'company_name' => trim($_POST['company_name']),
                'legal_name' => trim($_POST['legal_name']),
                'company_code' => trim($_POST['company_code']),
                'business_type' => trim($_POST['business_type'] ?? ''),
                'registration_number' => trim($_POST['registration_number'] ?? ''),
                'tax_id' => trim($_POST['tax_id'] ?? ''),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone'] ?? ''),
                'website' => trim($_POST['website'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'postal_code' => trim($_POST['postal_code'] ?? ''),
                'country' => trim($_POST['country'] ?? 'Tunisia'),
                'max_users' => intval($_POST['max_users'] ?? 5),
                'max_vehicles' => intval($_POST['max_vehicles'] ?? 10),
                'max_drivers' => intval($_POST['max_drivers'] ?? 10),
                'subscription_plan' => trim($_POST['subscription_plan'] ?? 'starter'),
                'subscription_status' => trim($_POST['subscription_status'] ?? 'active'),
                'status' => trim($_POST['status'] ?? 'active')
            ];

            // Validate
            if (empty($updateData['company_name'])) {
                $data['errors']['company_name'] = 'Nom de l\'entreprise requis';
            }

            if (empty($updateData['email'])) {
                $data['errors']['email'] = 'Email requis';
            } elseif (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
                $data['errors']['email'] = 'Email invalide';
            }

            // Check if company code already exists (excluding current company)
            if (!empty($updateData['company_code']) &&
                $updateData['company_code'] !== $company['company_code'] &&
                $this->companyModel->companyCodeExists($updateData['company_code'])) {
                $data['errors']['company_code'] = 'Ce code entreprise existe déjà';
            }

            // If no errors, update company
            if (empty($data['errors'])) {
                if ($this->companyModel->updateCompany($id, $updateData)) {
                    flash('success', 'Entreprise mise à jour avec succès');
                    $this->redirect('companies/view/' . $id);
                } else {
                    flash('error', 'Erreur lors de la mise à jour');
                }
            } else {
                $data['company'] = array_merge($company, $updateData);
            }
        }

        $this->view('companies/edit', $data);
    }

    /**
     * Delete company
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('companies');
        }

        $company = $this->companyModel->getCompanyById($id);

        if (!$company) {
            flash('error', 'Entreprise introuvable');
            $this->redirect('companies');
        }

        if ($this->companyModel->deleteCompany($id)) {
            flash('success', 'Entreprise supprimée avec succès');
        } else {
            flash('error', 'Erreur lors de la suppression');
        }

        $this->redirect('companies');
    }

    /**
     * Update company status
     */
    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('companies');
        }

        $status = $_POST['status'] ?? 'active';

        if ($this->companyModel->updateStatus($id, $status)) {
            flash('success', 'Statut mis à jour avec succès');
        } else {
            flash('error', 'Erreur lors de la mise à jour du statut');
        }

        $this->redirect('companies/view/' . $id);
    }

    /**
     * Update subscription status
     */
    public function updateSubscription($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('companies');
        }

        $subscriptionStatus = $_POST['subscription_status'] ?? 'active';

        if ($this->companyModel->updateSubscriptionStatus($id, $subscriptionStatus)) {
            flash('success', 'Abonnement mis à jour avec succès');
        } else {
            flash('error', 'Erreur lors de la mise à jour de l\'abonnement');
        }

        $this->redirect('companies/view/' . $id);
    }

    /**
     * Extend trial period
     */
    public function extendTrial($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('companies');
        }

        $days = intval($_POST['days'] ?? 30);

        if ($this->companyModel->extendTrial($id, $days)) {
            flash('success', "Période d'essai prolongée de {$days} jours");
        } else {
            flash('error', 'Erreur lors de la prolongation');
        }

        $this->redirect('companies/view/' . $id);
    }

    /**
     * Switch to a company (impersonate)
     */
    public function switchTo($id) {
        $company = $this->companyModel->getCompanyById($id);

        if (!$company) {
            flash('error', 'Entreprise introuvable');
            $this->redirect('companies');
        }

        // Store original super admin user ID for switching back
        if (!isset($_SESSION['original_user_id'])) {
            $_SESSION['original_user_id'] = $_SESSION['user_id'];
        }

        // Load company into session
        loadCompanyIntoSession($id);

        flash('info', 'Connecté en tant que: ' . $company['company_name']);
        $this->redirect('dashboard');
    }

    /**
     * Switch back to super admin
     */
    public function switchBack() {
        if (isset($_SESSION['original_user_id'])) {
            $_SESSION['user_id'] = $_SESSION['original_user_id'];
            unset($_SESSION['original_user_id']);
        }

        clearCompanyFromSession();
        $_SESSION['is_super_admin'] = true;

        flash('info', 'Retour au compte super admin');
        $this->redirect('companies');
    }

    /**
     * Company statistics dashboard
     */
    public function stats() {
        $stats = $this->companyModel->getGlobalStats();

        $data = [
            'title' => 'Statistiques Globales',
            'stats' => $stats
        ];

        $this->view('companies/stats', $data);
    }
}
