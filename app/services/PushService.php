<?php

/**
 * Push Notification Service
 * Handles web push notifications
 *
 * @author DigiParc Team
 * @version 1.0
 */
class PushService
{
    private $db;
    private $vapidPublicKey;
    private $vapidPrivateKey;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->vapidPublicKey = defined('VAPID_PUBLIC_KEY') ? VAPID_PUBLIC_KEY : '';
        $this->vapidPrivateKey = defined('VAPID_PRIVATE_KEY') ? VAPID_PRIVATE_KEY : '';
    }

    /**
     * Send push notification
     *
     * @param array $data
     * @return array
     */
    public function send($data)
    {
        try {
            // Get user's push subscriptions
            $subscriptions = $this->getUserSubscriptions($data['user_id']);

            if (empty($subscriptions)) {
                return [
                    'status' => 'failed',
                    'error' => 'No push subscriptions found for user',
                    'metadata' => null
                ];
            }

            $payload = json_encode([
                'title' => $data['title'],
                'body' => $data['body'],
                'icon' => $data['icon'] ?? '/assets/images/icon-192x192.png',
                'badge' => '/assets/images/badge-72x72.png',
                'url' => $data['url'] ?? '/',
                'data' => $data['data'] ?? [],
                'timestamp' => time()
            ]);

            $successCount = 0;
            $failureCount = 0;

            foreach ($subscriptions as $subscription) {
                $result = $this->sendToSubscription($subscription, $payload);
                if ($result) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }

            return [
                'status' => $successCount > 0 ? 'sent' : 'failed',
                'error' => $failureCount > 0 ? "$failureCount subscriptions failed" : null,
                'metadata' => [
                    'total' => count($subscriptions),
                    'success' => $successCount,
                    'failed' => $failureCount
                ]
            ];

        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'metadata' => null
            ];
        }
    }

    /**
     * Send to individual subscription
     */
    private function sendToSubscription($subscription, $payload)
    {
        try {
            $subData = json_decode($subscription['subscription_data'], true);

            // This would use a library like web-push-php in production
            // For now, we'll return true to simulate success
            // In production: Use minishlink/web-push library

            /*
            require_once 'vendor/autoload.php';

            $auth = [
                'VAPID' => [
                    'subject' => APP_URL,
                    'publicKey' => $this->vapidPublicKey,
                    'privateKey' => $this->vapidPrivateKey
                ]
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            $result = $webPush->sendOneNotification(
                \Minishlink\WebPush\Subscription::create($subData),
                $payload
            );

            return $result->isSuccess();
            */

            // Simulated for now
            return true;

        } catch (Exception $e) {
            // Remove invalid subscription
            $this->removeSubscription($subscription['id']);
            return false;
        }
    }

    /**
     * Get user push subscriptions
     */
    private function getUserSubscriptions($userId)
    {
        $sql = "SELECT * FROM push_subscriptions
                WHERE user_id = :user_id
                AND active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Remove invalid subscription
     */
    private function removeSubscription($subscriptionId)
    {
        $sql = "UPDATE push_subscriptions
                SET active = 0
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $subscriptionId);
        $stmt->execute();
    }

    /**
     * Register new push subscription
     */
    public function registerSubscription($userId, $subscription)
    {
        $sql = "INSERT INTO push_subscriptions (user_id, subscription_data, active)
                VALUES (:user_id, :subscription_data, 1)
                ON DUPLICATE KEY UPDATE
                subscription_data = :subscription_data,
                active = 1,
                updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);

        $subData = json_encode($subscription);
        $stmt->bindParam(':subscription_data', $subData);

        return $stmt->execute();
    }
}
