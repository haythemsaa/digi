<?php

class SubscriptionManager extends Controller {
    private $subscriptionModel;

    public function __construct() {
        $this->subscriptionModel = $this->model('SubscriptionManager');
    }

    // ========================================
    // ADMIN - MODULES MANAGEMENT
    // ========================================

    /**
     * List all modules (Admin)
     */
    public function modules() {
        $data = [
            'page_title' => 'Modules d\'Abonnement',
            'active_menu' => 'subscriptions',
            'modules' => $this->subscriptionModel->getAllModules()
        ];

        $this->view('subscription/admin/modules', $data);
    }

    /**
     * Create/Edit module form (Admin)
     */
    public function createModule($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $moduleData = [
                'module_code' => $_POST['module_code'],
                'module_name' => $_POST['module_name'],
                'description' => $_POST['description'] ?? null,
                'price_monthly' => $_POST['price_monthly'],
                'price_yearly' => $_POST['price_yearly'],
                'features' => !empty($_POST['features']) ? json_encode($_POST['features']) : null,
                'icon' => $_POST['icon'] ?? 'fa-star',
                'color' => $_POST['color'] ?? '#007bff',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => $_POST['sort_order'] ?? 0
            ];

            if ($id) {
                // Update existing module
                if ($this->subscriptionModel->updateModule($id, $moduleData)) {
                    flash('success', 'Module mis à jour avec succès');
                    redirect('subscription-manager/modules');
                } else {
                    flash('error', 'Erreur lors de la mise à jour du module');
                }
            } else {
                // Create new module
                if ($this->subscriptionModel->createModule($moduleData)) {
                    flash('success', 'Module créé avec succès');
                    redirect('subscription-manager/modules');
                } else {
                    flash('error', 'Erreur lors de la création du module');
                }
            }
        }

        $data = [
            'page_title' => $id ? 'Modifier le Module' : 'Nouveau Module',
            'active_menu' => 'subscriptions',
            'module' => $id ? $this->subscriptionModel->getModuleById($id) : null
        ];

        $this->view('subscription/admin/create_module', $data);
    }

    // ========================================
    // ADMIN - PACKS MANAGEMENT
    // ========================================

    /**
     * List all packs (Admin)
     */
    public function packs() {
        $packs = $this->subscriptionModel->getAllPacks();

        // Get modules for each pack
        foreach ($packs as &$pack) {
            $pack['modules'] = $this->subscriptionModel->getPackModules($pack['id']);
        }

        $data = [
            'page_title' => 'Packs d\'Abonnement',
            'active_menu' => 'subscriptions',
            'packs' => $packs
        ];

        $this->view('subscription/admin/packs', $data);
    }

    /**
     * Create/Edit pack form (Admin)
     */
    public function createPack($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $packData = [
                'pack_code' => $_POST['pack_code'],
                'pack_name' => $_POST['pack_name'],
                'description' => $_POST['description'] ?? null,
                'price_monthly' => $_POST['price_monthly'],
                'price_yearly' => $_POST['price_yearly'],
                'discount_percent' => $_POST['discount_percent'] ?? 0,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => $_POST['sort_order'] ?? 0
            ];

            $moduleIds = $_POST['modules'] ?? [];

            if ($id) {
                // Update existing pack
                if ($this->subscriptionModel->updatePack($id, $packData, $moduleIds)) {
                    flash('success', 'Pack mis à jour avec succès');
                    redirect('subscription-manager/packs');
                } else {
                    flash('error', 'Erreur lors de la mise à jour du pack');
                }
            } else {
                // Create new pack
                if ($this->subscriptionModel->createPack($packData, $moduleIds)) {
                    flash('success', 'Pack créé avec succès');
                    redirect('subscription-manager/packs');
                } else {
                    flash('error', 'Erreur lors de la création du pack');
                }
            }
        }

        $data = [
            'page_title' => $id ? 'Modifier le Pack' : 'Nouveau Pack',
            'active_menu' => 'subscriptions',
            'pack' => $id ? $this->subscriptionModel->getPackById($id) : null,
            'all_modules' => $this->subscriptionModel->getAllModules(true)
        ];

        $this->view('subscription/admin/create_pack', $data);
    }

    // ========================================
    // ADMIN - COMPANY SUBSCRIPTIONS MANAGEMENT
    // ========================================

    /**
     * List all company subscriptions (Admin)
     */
    public function companySubscriptions($companyId = null) {
        if ($companyId) {
            $subscriptions = $this->subscriptionModel->getCompanySubscriptions($companyId);
            $stats = $this->subscriptionModel->getSubscriptionStats($companyId);
        } else {
            // List all subscriptions from all companies
            $subscriptions = []; // TODO: Implement getAllSubscriptions if needed
            $stats = $this->subscriptionModel->getSubscriptionStats();
        }

        $data = [
            'page_title' => 'Abonnements Entreprises',
            'active_menu' => 'subscriptions',
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'company_id' => $companyId
        ];

        $this->view('subscription/admin/company_subscriptions', $data);
    }

    /**
     * Subscribe a company to module/pack (Admin)
     */
    public function subscribeCompany($companyId) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $subscriptionType = $_POST['subscription_type']; // 'module' or 'pack'
            $billingCycle = $_POST['billing_cycle'] ?? 'monthly';

            $subscriptionId = false;

            if ($subscriptionType === 'module' && !empty($_POST['module_id'])) {
                $subscriptionId = $this->subscriptionModel->subscribeToModule(
                    $companyId,
                    $_POST['module_id'],
                    $billingCycle
                );
            } elseif ($subscriptionType === 'pack' && !empty($_POST['pack_id'])) {
                $subscriptionId = $this->subscriptionModel->subscribeToPack(
                    $companyId,
                    $_POST['pack_id'],
                    $billingCycle
                );
            }

            if ($subscriptionId) {
                // Generate invoice
                $this->subscriptionModel->generateInvoice($subscriptionId);

                flash('success', 'Abonnement activé avec succès');
            } else {
                flash('error', 'Erreur lors de l\'activation de l\'abonnement');
            }

            redirect('subscription-manager/companySubscriptions/' . $companyId);
        }

        $data = [
            'page_title' => 'Nouvel Abonnement',
            'active_menu' => 'subscriptions',
            'company_id' => $companyId,
            'modules' => $this->subscriptionModel->getAllModules(true),
            'packs' => $this->subscriptionModel->getAllPacks(true)
        ];

        $this->view('subscription/admin/subscribe_company', $data);
    }

    /**
     * Cancel subscription (Admin)
     */
    public function cancelSubscription($subscriptionId) {
        if ($this->subscriptionModel->cancelSubscription($subscriptionId)) {
            flash('success', 'Abonnement annulé');
        } else {
            flash('error', 'Erreur lors de l\'annulation');
        }

        // Redirect back
        redirect($_SERVER['HTTP_REFERER'] ?? 'subscription-manager/companySubscriptions');
    }

    /**
     * Suspend subscription (Admin)
     */
    public function suspendSubscription($subscriptionId) {
        $reason = $_POST['reason'] ?? null;

        if ($this->subscriptionModel->suspendSubscription($subscriptionId, $reason)) {
            flash('success', 'Abonnement suspendu');
        } else {
            flash('error', 'Erreur lors de la suspension');
        }

        redirect($_SERVER['HTTP_REFERER'] ?? 'subscription-manager/companySubscriptions');
    }

    /**
     * Reactivate subscription (Admin)
     */
    public function reactivateSubscription($subscriptionId) {
        if ($this->subscriptionModel->reactivateSubscription($subscriptionId)) {
            flash('success', 'Abonnement réactivé');
        } else {
            flash('error', 'Erreur lors de la réactivation');
        }

        redirect($_SERVER['HTTP_REFERER'] ?? 'subscription-manager/companySubscriptions');
    }

    // ========================================
    // CLIENT - SUBSCRIPTION PAGES
    // ========================================

    /**
     * View available subscription plans (Client)
     */
    public function index() {
        $data = [
            'page_title' => 'Plans d\'Abonnement',
            'active_menu' => 'subscriptions',
            'modules' => $this->subscriptionModel->getAllModules(true),
            'packs' => $this->subscriptionModel->getAllPacks(true)
        ];

        // Get modules for each pack
        foreach ($data['packs'] as &$pack) {
            $pack['modules'] = $this->subscriptionModel->getPackModules($pack['id']);
        }

        $this->view('subscription/client/index', $data);
    }

    /**
     * View my subscriptions (Client)
     */
    public function mySubscription() {
        // For now, using company_id = 1 as default
        // In production, get from session
        $companyId = $_SESSION['company_id'] ?? 1;

        $data = [
            'page_title' => 'Mon Abonnement',
            'active_menu' => 'my_subscription',
            'subscriptions' => $this->subscriptionModel->getCompanySubscriptions($companyId),
            'active_modules' => $this->subscriptionModel->getCompanyActiveModules($companyId),
            'stats' => $this->subscriptionModel->getSubscriptionStats($companyId),
            'invoices' => $this->subscriptionModel->getCompanyInvoices($companyId)
        ];

        $this->view('subscription/client/my_subscription', $data);
    }

    /**
     * Subscribe to module/pack (Client)
     */
    public function subscribe() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $companyId = $_SESSION['company_id'] ?? 1;
            $subscriptionType = $_POST['subscription_type'];
            $billingCycle = $_POST['billing_cycle'] ?? 'monthly';

            $subscriptionId = false;

            if ($subscriptionType === 'module' && !empty($_POST['module_id'])) {
                $subscriptionId = $this->subscriptionModel->subscribeToModule(
                    $companyId,
                    $_POST['module_id'],
                    $billingCycle
                );
            } elseif ($subscriptionType === 'pack' && !empty($_POST['pack_id'])) {
                $subscriptionId = $this->subscriptionModel->subscribeToPack(
                    $companyId,
                    $_POST['pack_id'],
                    $billingCycle
                );
            }

            if ($subscriptionId) {
                // Generate invoice
                $this->subscriptionModel->generateInvoice($subscriptionId);

                flash('success', 'Abonnement activé avec succès! Une facture a été générée.');
                redirect('subscription-manager/mySubscription');
            } else {
                flash('error', 'Erreur lors de l\'activation de l\'abonnement');
                redirect('subscription-manager');
            }
        }
    }

    /**
     * Cancel my subscription (Client)
     */
    public function cancelMySubscription($subscriptionId) {
        $companyId = $_SESSION['company_id'] ?? 1;

        // Verify ownership
        $subscription = $this->subscriptionModel->getSubscriptionByIdAndCompany($subscriptionId, $companyId);

        if (!$subscription) {
            flash('error', 'Abonnement non trouvé');
            redirect('subscription-manager/mySubscription');
        }

        if ($this->subscriptionModel->cancelSubscription($subscriptionId)) {
            flash('success', 'Votre abonnement a été annulé');
        } else {
            flash('error', 'Erreur lors de l\'annulation');
        }

        redirect('subscription-manager/mySubscription');
    }

    // ========================================
    // INVOICING
    // ========================================

    /**
     * View invoice
     */
    public function viewInvoice($invoiceId) {
        $companyId = $_SESSION['company_id'] ?? 1;

        $invoice = $this->subscriptionModel->getInvoiceByIdAndCompany($invoiceId, $companyId);

        if (!$invoice) {
            flash('error', 'Facture non trouvée');
            redirect('subscription-manager/mySubscription');
        }

        $data = [
            'page_title' => 'Facture ' . $invoice['invoice_number'],
            'active_menu' => 'my_subscription',
            'invoice' => $invoice
        ];

        $this->view('subscription/client/view_invoice', $data);
    }

    /**
     * Mark invoice as paid (Admin only)
     */
    public function markInvoicePaid($invoiceId) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $paymentMethod = $_POST['payment_method'] ?? null;
            $paymentReference = $_POST['payment_reference'] ?? null;

            if ($this->subscriptionModel->markInvoicePaid($invoiceId, $paymentMethod, $paymentReference)) {
                flash('success', 'Facture marquée comme payée');
            } else {
                flash('error', 'Erreur lors de la mise à jour');
            }
        }

        redirect($_SERVER['HTTP_REFERER'] ?? 'subscription-manager/mySubscription');
    }

    // ========================================
    // API ENDPOINTS
    // ========================================

    /**
     * Check module access (API)
     */
    public function apiCheckAccess() {
        header('Content-Type: application/json');

        $companyId = $_GET['company_id'] ?? ($_SESSION['company_id'] ?? null);
        $moduleCode = $_GET['module_code'] ?? null;

        if (!$companyId || !$moduleCode) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            return;
        }

        $hasAccess = $this->subscriptionModel->hasModuleAccess($companyId, $moduleCode);

        echo json_encode([
            'success' => true,
            'has_access' => $hasAccess,
            'module_code' => $moduleCode
        ]);
    }

    /**
     * Get active modules (API)
     */
    public function apiGetActiveModules() {
        header('Content-Type: application/json');

        $companyId = $_GET['company_id'] ?? ($_SESSION['company_id'] ?? null);

        if (!$companyId) {
            echo json_encode(['success' => false, 'message' => 'Missing company_id']);
            return;
        }

        $modules = $this->subscriptionModel->getCompanyActiveModules($companyId);

        echo json_encode([
            'success' => true,
            'modules' => $modules
        ]);
    }
}
