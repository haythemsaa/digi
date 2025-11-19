<?php

/**
 * Stripe Payment Service
 * Handles Stripe payment integration
 *
 * @author DigiParc Team
 * @version 1.0
 */
class StripeService
{
    private $apiKey;
    private $webhookSecret;

    public function __construct()
    {
        $this->apiKey = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';
        $this->webhookSecret = defined('STRIPE_WEBHOOK_SECRET') ? STRIPE_WEBHOOK_SECRET : '';

        // Set Stripe API key
        if (!empty($this->apiKey)) {
            \Stripe\Stripe::setApiKey($this->apiKey);
        }
    }

    /**
     * Create checkout session for subscription
     *
     * @param int $companyId
     * @param string $planId Plan identifier
     * @return array Session data
     */
    public function createCheckoutSession($companyId, $planId)
    {
        $company = $this->getCompany($companyId);

        $priceId = $this->getPriceIdForPlan($planId);

        if (!$priceId) {
            return [
                'success' => false,
                'error' => 'Invalid plan ID'
            ];
        }

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => APP_URL . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => APP_URL . '/payment/cancel',
                'client_reference_id' => $companyId,
                'customer_email' => $company['email'],
                'metadata' => [
                    'company_id' => $companyId,
                    'plan_id' => $planId
                ]
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'url' => $session->url
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle webhook events
     *
     * @param string $payload Webhook payload
     * @param string $signature Stripe signature header
     * @return array
     */
    public function handleWebhook($payload, $signature)
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $this->webhookSecret
            );

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Webhook signature verification failed'
            ];
        }

        // Handle different event types
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event->data->object);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionCancelled($event->data->object);
                break;

            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
        }

        return ['success' => true];
    }

    /**
     * Handle checkout completed
     */
    private function handleCheckoutCompleted($session)
    {
        $companyId = $session->metadata->company_id;
        $planId = $session->metadata->plan_id;

        $db = Database::getInstance()->getConnection();

        $sql = "UPDATE companies
                SET subscription_plan = :plan,
                    subscription_status = 'active',
                    stripe_customer_id = :customer_id,
                    stripe_subscription_id = :subscription_id,
                    subscription_start_date = NOW()
                WHERE id = :company_id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':plan', $planId);
        $stmt->bindParam(':customer_id', $session->customer);
        $stmt->bindParam(':subscription_id', $session->subscription);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        // Log payment
        $this->logPayment($companyId, $session->amount_total / 100, 'succeeded', $session->id);
    }

    /**
     * Handle subscription updated
     */
    private function handleSubscriptionUpdated($subscription)
    {
        $db = Database::getInstance()->getConnection();

        $sql = "UPDATE companies
                SET subscription_status = :status
                WHERE stripe_subscription_id = :subscription_id";

        $stmt = $db->prepare($sql);
        $status = $subscription->status;
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':subscription_id', $subscription->id);
        $stmt->execute();
    }

    /**
     * Handle subscription cancelled
     */
    private function handleSubscriptionCancelled($subscription)
    {
        $db = Database::getInstance()->getConnection();

        $sql = "UPDATE companies
                SET subscription_status = 'cancelled',
                    subscription_end_date = NOW()
                WHERE stripe_subscription_id = :subscription_id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':subscription_id', $subscription->id);
        $stmt->execute();
    }

    /**
     * Handle payment succeeded
     */
    private function handlePaymentSucceeded($invoice)
    {
        $db = Database::getInstance()->getConnection();

        // Get company ID from customer
        $sql = "SELECT id FROM companies WHERE stripe_customer_id = :customer_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':customer_id', $invoice->customer);
        $stmt->execute();
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($company) {
            $this->logPayment($company['id'], $invoice->amount_paid / 100, 'succeeded', $invoice->id);
        }
    }

    /**
     * Handle payment failed
     */
    private function handlePaymentFailed($invoice)
    {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT id FROM companies WHERE stripe_customer_id = :customer_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':customer_id', $invoice->customer);
        $stmt->execute();
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($company) {
            $this->logPayment($company['id'], $invoice->amount_due / 100, 'failed', $invoice->id);

            // Update subscription status
            $sql = "UPDATE companies
                    SET subscription_status = 'past_due'
                    WHERE id = :company_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':company_id', $company['id']);
            $stmt->execute();
        }
    }

    /**
     * Cancel subscription
     *
     * @param int $companyId
     * @return array
     */
    public function cancelSubscription($companyId)
    {
        $company = $this->getCompany($companyId);

        if (empty($company['stripe_subscription_id'])) {
            return [
                'success' => false,
                'error' => 'No active subscription found'
            ];
        }

        try {
            \Stripe\Subscription::update(
                $company['stripe_subscription_id'],
                ['cancel_at_period_end' => true]
            );

            return ['success' => true];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get price ID for plan
     */
    private function getPriceIdForPlan($planId)
    {
        $prices = [
            'starter' => defined('STRIPE_PRICE_STARTER') ? STRIPE_PRICE_STARTER : null,
            'professional' => defined('STRIPE_PRICE_PRO') ? STRIPE_PRICE_PRO : null,
            'enterprise' => defined('STRIPE_PRICE_ENTERPRISE') ? STRIPE_PRICE_ENTERPRISE : null,
        ];

        return $prices[$planId] ?? null;
    }

    /**
     * Log payment
     */
    private function logPayment($companyId, $amount, $status, $transactionId)
    {
        $db = Database::getInstance()->getConnection();

        $sql = "INSERT INTO payments (company_id, amount, status, transaction_id, payment_method, created_at)
                VALUES (:company_id, :amount, :status, :transaction_id, 'stripe', NOW())";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':transaction_id', $transactionId);
        $stmt->execute();
    }

    /**
     * Get company
     */
    private function getCompany($companyId)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM companies WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $companyId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
