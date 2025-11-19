<?php

/**
 * Authentication API Controller
 * Handles user authentication via API
 *
 * @author Pakiparc Team
 * @version 1.0
 */

class AuthAPI
{
    /**
     * Login endpoint
     */
    public function login($params = [])
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['email']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode([
                'error' => true,
                'message' => 'Email and password are required'
            ]);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':email', $input['email']);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($input['password'], $user['password'])) {
                http_response_code(401);
                echo json_encode([
                    'error' => true,
                    'message' => 'Invalid credentials'
                ]);
                return;
            }

            // Generate JWT token
            $token = $this->generateJWT($user);

            // Update last login
            $updateSql = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->bindParam(':id', $user['id']);
            $updateStmt->execute();

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'role' => $user['role'],
                        'company_id' => $user['company_id']
                    ]
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Server error',
                'details' => DEBUG ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Logout endpoint
     */
    public function logout($params = [])
    {
        // In a real implementation, you might want to blacklist the token
        http_response_code(200);
        echo json_encode([
            'error' => false,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Refresh token endpoint
     */
    public function refresh($params = [])
    {
        // Get current user from token
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'error' => true,
                'message' => 'Unauthorized'
            ]);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT * FROM users WHERE id = :id AND status = 'active' LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                http_response_code(401);
                echo json_encode([
                    'error' => true,
                    'message' => 'User not found'
                ]);
                return;
            }

            // Generate new JWT token
            $token = $this->generateJWT($user);

            http_response_code(200);
            echo json_encode([
                'error' => false,
                'message' => 'Token refreshed',
                'data' => [
                    'token' => $token
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'message' => 'Server error',
                'details' => DEBUG ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Generate JWT token
     */
    private function generateJWT($user)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        $payload = json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'company_id' => $user['company_id'],
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ]);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
