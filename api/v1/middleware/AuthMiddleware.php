<?php

/**
 * API Authentication Middleware
 * Validates JWT tokens for API requests
 *
 * @author DigiParc Team
 * @version 1.0
 */
class AuthMiddleware
{
    public static function handle()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            self::unauthorized('Missing or invalid authorization header');
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        $user = self::validateToken($token);

        if (!$user) {
            self::unauthorized('Invalid or expired token');
        }

        // Set user in global context
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['role'] = $user['role'];
    }

    /**
     * Validate JWT token
     */
    private static function validateToken($token)
    {
        // Simple token validation (in production, use proper JWT library)
        // This is a placeholder - implement real JWT validation

        try {
            $db = Database::getInstance()->getConnection();

            // Check token in database (API tokens table)
            $sql = "SELECT u.*, t.token
                    FROM api_tokens t
                    JOIN users u ON t.user_id = u.id
                    WHERE t.token = :token
                    AND t.expires_at > NOW()
                    AND t.revoked = 0";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Return unauthorized response
     */
    private static function unauthorized($message = 'Unauthorized')
    {
        http_response_code(401);
        echo json_encode([
            'error' => true,
            'message' => $message
        ]);
        exit;
    }
}
