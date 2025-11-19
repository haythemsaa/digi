<?php

/**
 * Subscription Model
 * Handles subscription and payment management
 *
 * @author Pakiparc Team
 * @version 1.0
 */

class Subscription
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get company subscription
     */
    public function getCompanySubscription($companyId)
    {
        $sql = "SELECT * FROM subscriptions
                WHERE company_id = :company_id
                AND status = 'active'
                ORDER BY created_at DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create or update subscription
     */
    public function createSubscription($companyId, $data)
    {
        $sql = "INSERT INTO subscriptions
                (company_id, plan_id, stripe_subscription_id, stripe_customer_id,
                 status, current_period_start, current_period_end, created_at)
                VALUES
                (:company_id, :plan_id, :stripe_subscription_id, :stripe_customer_id,
                 :status, :current_period_start, :current_period_end, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':plan_id', $data['plan_id']);
        $stmt->bindParam(':stripe_subscription_id', $data['stripe_subscription_id']);
        $stmt->bindParam(':stripe_customer_id', $data['stripe_customer_id']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':current_period_start', $data['current_period_start']);
        $stmt->bindParam(':current_period_end', $data['current_period_end']);

        $stmt->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Update subscription
     */
    public function updateSubscription($subscriptionId, $data)
    {
        $fields = [];
        $params = [':id' => $subscriptionId];

        $allowedFields = ['status', 'plan_id', 'current_period_start', 'current_period_end', 'cancelled_at'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE subscriptions SET " . implode(', ', $fields) . ", updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        $sql = "UPDATE subscriptions SET
                status = 'cancelled',
                cancelled_at = NOW(),
                updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $subscriptionId);

        return $stmt->execute();
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory($companyId, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM payments
                WHERE company_id = :company_id
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create payment record
     */
    public function createPayment($companyId, $data)
    {
        $sql = "INSERT INTO payments
                (company_id, subscription_id, stripe_payment_intent_id, amount, currency,
                 status, metadata, created_at)
                VALUES
                (:company_id, :subscription_id, :stripe_payment_intent_id, :amount, :currency,
                 :status, :metadata, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':subscription_id', $data['subscription_id']);
        $stmt->bindParam(':stripe_payment_intent_id', $data['stripe_payment_intent_id']);
        $stmt->bindParam(':amount', $data['amount']);
        $stmt->bindParam(':currency', $data['currency']);
        $stmt->bindParam(':status', $data['status']);
        $metadata = json_encode($data['metadata'] ?? []);
        $stmt->bindParam(':metadata', $metadata);

        $stmt->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($paymentIntentId, $status)
    {
        $sql = "UPDATE payments SET
                status = :status,
                updated_at = NOW()
                WHERE stripe_payment_intent_id = :payment_intent_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':payment_intent_id', $paymentIntentId);

        return $stmt->execute();
    }

    /**
     * Get subscription plans
     */
    public function getPlans()
    {
        $sql = "SELECT * FROM subscription_plans WHERE active = 1 ORDER BY price ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get plan by ID
     */
    public function getPlan($planId)
    {
        $sql = "SELECT * FROM subscription_plans WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $planId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if company has active subscription
     */
    public function hasActiveSubscription($companyId)
    {
        $sql = "SELECT COUNT(*) as count FROM subscriptions
                WHERE company_id = :company_id
                AND status = 'active'
                AND current_period_end > NOW()";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Get usage for current period
     */
    public function getCurrentUsage($companyId)
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM users WHERE company_id = :company_id AND status = 'active') as active_users,
                    (SELECT COUNT(*) FROM vehicles WHERE company_id = :company_id) as total_vehicles,
                    (SELECT COUNT(*) FROM drivers WHERE company_id = :company_id) as total_drivers,
                    (SELECT COUNT(*) FROM missions WHERE company_id = :company_id
                     AND start_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) as monthly_missions";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
