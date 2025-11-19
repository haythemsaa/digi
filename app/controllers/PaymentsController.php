<?php

/**
 * Payments Controller
 * Handles payment and subscription management
 *
 * @author Pakiparc Team
 * @version 1.0
 */

require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../services/StripeService.php';
require_once __DIR__ . '/../services/PricingService.php';

class PaymentsController
{
    private $subscriptionModel;
    private $stripeService;
    private $pricingService;

    public function __construct()
    {
        $this->subscriptionModel = new Subscription();
        $this->stripeService = new StripeService();
        $this->pricingService = new PricingService();
    }

    /**
     * Display subscription overview
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $companyId = $_SESSION['company_id'];
        $subscription = $this->subscriptionModel->getCompanySubscription($companyId);
        $usage = $this->subscriptionModel->getCurrentUsage($companyId);
        $pricing = $this->pricingService->calculateMonthlyPrice($companyId);

        $data = [
            'subscription' => $subscription,
            'usage' => $usage,
            'pricing' => $pricing
        ];

        require_once __DIR__ . '/../views/payments/index.php';
    }

    /**
     * Display payment history
     */
    public function history()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $companyId = $_SESSION['company_id'];
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $payments = $this->subscriptionModel->getPaymentHistory($companyId, $page, 20);

        $data = [
            'payments' => $payments,
            'page' => $page
        ];

        require_once __DIR__ . '/../views/payments/history.php';
    }

    /**
     * Choose subscription plan
     */
    public function choosePlan()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $plans = $this->subscriptionModel->getPlans();

        $data = ['plans' => $plans];

        require_once __DIR__ . '/../views/payments/choose_plan.php';
    }

    /**
     * Create checkout session
     */
    public function checkout()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /payments');
            exit;
        }

        $planId = $_POST['plan_id'] ?? null;
        if (!$planId) {
            $_SESSION['error'] = 'Please select a plan';
            header('Location: /payments/choose-plan');
            exit;
        }

        $companyId = $_SESSION['company_id'];

        try {
            $session = $this->stripeService->createCheckoutSession($companyId, $planId);

            // Redirect to Stripe Checkout
            header('Location: ' . $session['url']);
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to create checkout session: ' . $e->getMessage();
            header('Location: /payments/choose-plan');
            exit;
        }
    }

    /**
     * Payment success callback
     */
    public function success()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $sessionId = $_GET['session_id'] ?? null;
        if (!$sessionId) {
            header('Location: /payments');
            exit;
        }

        // Retrieve session from Stripe to verify
        try {
            $session = $this->stripeService->retrieveSession($sessionId);

            $_SESSION['success'] = 'Payment successful! Your subscription is now active.';
            header('Location: /payments');
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to verify payment';
            header('Location: /payments');
            exit;
        }
    }

    /**
     * Payment cancel callback
     */
    public function cancel()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $_SESSION['info'] = 'Payment cancelled. You can try again anytime.';
        header('Location: /payments/choose-plan');
        exit;
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /payments');
            exit;
        }

        $companyId = $_SESSION['company_id'];
        $subscription = $this->subscriptionModel->getCompanySubscription($companyId);

        if (!$subscription) {
            $_SESSION['error'] = 'No active subscription found';
            header('Location: /payments');
            exit;
        }

        try {
            // Cancel in Stripe
            $this->stripeService->cancelSubscription($subscription['stripe_subscription_id']);

            // Update local database
            $this->subscriptionModel->cancelSubscription($subscription['id']);

            $_SESSION['success'] = 'Subscription cancelled successfully';
            header('Location: /payments');
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to cancel subscription: ' . $e->getMessage();
            header('Location: /payments');
            exit;
        }
    }

    /**
     * Stripe webhook handler
     */
    public function webhook()
    {
        $payload = @file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $this->stripeService->handleWebhook($payload, $signature);

            http_response_code(200);
            echo json_encode(['received' => true]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
