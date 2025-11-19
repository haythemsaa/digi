<?php

/**
 * GDPR Controller
 * Handles GDPR/RGPD compliance features
 *
 * @author Pakiparc Team
 * @version 1.0
 */
class GDPRController
{
    private $gdprService;
    private $companyId;
    private $userId;

    public function __construct()
    {
        requireAuth();
        CompanyMiddleware::requireCompanyContext();

        $this->gdprService = new GDPRService();
        $this->companyId = getCurrentCompanyId();
        $this->userId = $_SESSION['user_id'];
    }

    /**
     * GDPR dashboard
     */
    public function dashboard()
    {
        // Only admins can access
        if (!in_array($_SESSION['role'], ['admin', 'super_admin'])) {
            flash('error', 'Accès refusé');
            redirect('dashboard');
        }

        $report = $this->gdprService->generateComplianceReport($this->companyId);
        $company = getCurrentCompany();

        $data = [
            'title' => 'Conformité RGPD',
            'report' => $report,
            'company' => $company
        ];

        view('gdpr/dashboard', $data);
    }

    /**
     * Request data export
     */
    public function requestDataExport()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestId = $this->gdprService->createRequest($this->userId, 'data_export');

            if ($requestId) {
                // Process immediately for current user
                $userData = $this->gdprService->exportUserData($this->userId);

                // Save as JSON
                $jsonData = json_encode($userData, JSON_PRETTY_PRINT);

                // Update request with data
                $this->updateRequestResponse($requestId, $jsonData);

                // Send as download
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="my_data_' . date('Y-m-d') . '.json"');
                echo $jsonData;
                exit;
            } else {
                flash('error', 'Erreur lors de la création de la demande');
                redirect('gdpr/my-data');
            }
        }

        view('gdpr/request_export');
    }

    /**
     * Request data deletion
     */
    public function requestDataDeletion()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $confirmation = $_POST['confirmation'] ?? '';

            if ($confirmation !== 'DELETE') {
                flash('error', 'Veuillez taper DELETE pour confirmer');
                redirect('gdpr/request-deletion');
            }

            $requestId = $this->gdprService->createRequest(
                $this->userId,
                'data_deletion',
                'User requested account deletion'
            );

            if ($requestId) {
                flash('success', 'Votre demande de suppression a été enregistrée. Elle sera traitée sous 30 jours.');
                redirect('dashboard');
            } else {
                flash('error', 'Erreur lors de la création de la demande');
                redirect('gdpr/request-deletion');
            }
        }

        view('gdpr/request_deletion');
    }

    /**
     * Manage consents
     */
    public function manageConsents()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cookies = isset($_POST['cookies']) ? 1 : 0;
            $marketing = isset($_POST['marketing']) ? 1 : 0;
            $analytics = isset($_POST['analytics']) ? 1 : 0;

            $this->gdprService->recordConsent($this->userId, 'cookies', $cookies, 'Cookies fonctionnels et techniques');
            $this->gdprService->recordConsent($this->userId, 'marketing', $marketing, 'Communications marketing');
            $this->gdprService->recordConsent($this->userId, 'analytics', $analytics, 'Analyse et statistiques');

            flash('success', 'Vos préférences ont été enregistrées');
            redirect('gdpr/consents');
        }

        $consents = [
            'cookies' => $this->gdprService->hasConsent($this->userId, 'cookies'),
            'marketing' => $this->gdprService->hasConsent($this->userId, 'marketing'),
            'analytics' => $this->gdprService->hasConsent($this->userId, 'analytics')
        ];

        $data = [
            'title' => 'Gestion des Consentements',
            'consents' => $consents
        ];

        view('gdpr/consents', $data);
    }

    /**
     * Privacy policy page
     */
    public function privacyPolicy()
    {
        $company = getCurrentCompany();

        $data = [
            'title' => 'Politique de Confidentialité',
            'company' => $company
        ];

        view('gdpr/privacy_policy', $data);
    }

    /**
     * Cookie policy page
     */
    public function cookiePolicy()
    {
        view('gdpr/cookie_policy');
    }

    /**
     * Legal notices
     */
    public function legalNotices()
    {
        $company = getCurrentCompany();

        $data = [
            'title' => 'Mentions Légales',
            'company' => $company
        ];

        view('gdpr/legal_notices', $data);
    }

    /**
     * Process GDPR request (admin only)
     */
    public function processRequest($requestId)
    {
        if (!in_array($_SESSION['role'], ['admin', 'super_admin'])) {
            flash('error', 'Accès refusé');
            redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'approve') {
                $request = $this->getRequest($requestId);

                if ($request['request_type'] === 'data_deletion') {
                    $success = $this->gdprService->deleteUserData($request['user_id']);
                    if ($success) {
                        $this->markRequestCompleted($requestId);
                        flash('success', 'Données supprimées avec succès');
                    } else {
                        flash('error', 'Erreur lors de la suppression');
                    }
                }
            } elseif ($action === 'reject') {
                $reason = $_POST['reason'] ?? '';
                $this->markRequestRejected($requestId, $reason);
                flash('success', 'Demande rejetée');
            }

            redirect('gdpr/dashboard');
        }
    }

    /**
     * Helper: Update request response
     */
    private function updateRequestResponse($requestId, $response)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE gdpr_requests
                SET response_data = :response,
                    status = 'completed',
                    completed_at = NOW()
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':response', $response);
        $stmt->bindParam(':id', $requestId);
        $stmt->execute();
    }

    /**
     * Helper: Get request
     */
    private function getRequest($requestId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM gdpr_requests WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $requestId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Mark request completed
     */
    private function markRequestCompleted($requestId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE gdpr_requests
                SET status = 'completed',
                    processed_by = :processor,
                    processed_at = NOW(),
                    completed_at = NOW()
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':processor', $this->userId);
        $stmt->bindParam(':id', $requestId);
        $stmt->execute();
    }

    /**
     * Helper: Mark request rejected
     */
    private function markRequestRejected($requestId, $reason)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE gdpr_requests
                SET status = 'rejected',
                    processed_by = :processor,
                    processed_at = NOW(),
                    rejection_reason = :reason
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':processor', $this->userId);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':id', $requestId);
        $stmt->execute();
    }
}
