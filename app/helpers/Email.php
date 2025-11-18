<?php
/**
 * Email Helper Class
 * For sending emails via SMTP
 */

class Email {

    /**
     * Send email
     */
    public static function send($to, $subject, $body, $isHTML = true) {
        // In production, use PHPMailer or similar
        // This is a simplified version

        $headers = [];

        if ($isHTML) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=utf-8';
        }

        $headers[] = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM . '>';
        $headers[] = 'Reply-To: ' . SMTP_FROM;
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Send welcome email
     */
    public static function sendWelcome($user) {
        $subject = 'Welcome to ' . APP_NAME;
        $body = self::getTemplate('welcome', [
            'user' => $user,
            'app_name' => APP_NAME,
            'app_url' => APP_URL
        ]);

        return self::send($user['email'], $subject, $body);
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordReset($user, $resetToken) {
        $subject = 'Password Reset Request';
        $resetLink = APP_URL . '/reset-password?token=' . $resetToken;

        $body = self::getTemplate('password_reset', [
            'user' => $user,
            'reset_link' => $resetLink,
            'app_name' => APP_NAME
        ]);

        return self::send($user['email'], $subject, $body);
    }

    /**
     * Send maintenance alert
     */
    public static function sendMaintenanceAlert($user, $vehicle, $maintenance) {
        $subject = 'Maintenance Alert - ' . $vehicle['registration_number'];

        $body = self::getTemplate('maintenance_alert', [
            'user' => $user,
            'vehicle' => $vehicle,
            'maintenance' => $maintenance,
            'app_name' => APP_NAME,
            'app_url' => APP_URL
        ]);

        return self::send($user['email'], $subject, $body);
    }

    /**
     * Send document expiry alert
     */
    public static function sendDocumentExpiryAlert($user, $vehicle, $documentType, $expiryDate) {
        $subject = 'Document Expiry Alert - ' . $vehicle['registration_number'];

        $body = self::getTemplate('document_expiry', [
            'user' => $user,
            'vehicle' => $vehicle,
            'document_type' => $documentType,
            'expiry_date' => $expiryDate,
            'app_name' => APP_NAME
        ]);

        return self::send($user['email'], $subject, $body);
    }

    /**
     * Get email template
     */
    private static function getTemplate($template, $data) {
        $templatePath = APP_PATH . '/views/emails/' . $template . '.php';

        if (file_exists($templatePath)) {
            extract($data);
            ob_start();
            include $templatePath;
            return ob_get_clean();
        }

        // Fallback to simple template
        return self::getSimpleTemplate($template, $data);
    }

    /**
     * Simple email template fallback
     */
    private static function getSimpleTemplate($template, $data) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . APP_NAME . '</h1>
        </div>
        <div class="content">';

        switch ($template) {
            case 'welcome':
                $html .= '<h2>Welcome, ' . $data['user']['first_name'] . '!</h2>
                    <p>Thank you for joining ' . APP_NAME . '. Your account has been successfully created.</p>
                    <a href="' . $data['app_url'] . '" class="button">Go to Dashboard</a>';
                break;

            case 'maintenance_alert':
                $html .= '<h2>Maintenance Alert</h2>
                    <p>Vehicle <strong>' . $data['vehicle']['registration_number'] . '</strong> has maintenance due.</p>
                    <p><strong>Type:</strong> ' . $data['maintenance']['type'] . '</p>
                    <p><strong>Due Date:</strong> ' . $data['maintenance']['due_date'] . '</p>
                    <a href="' . $data['app_url'] . '/maintenance" class="button">View Details</a>';
                break;

            case 'document_expiry':
                $html .= '<h2>Document Expiry Alert</h2>
                    <p>The ' . $data['document_type'] . ' for vehicle <strong>' . $data['vehicle']['registration_number'] . '</strong> is expiring soon.</p>
                    <p><strong>Expiry Date:</strong> ' . date('d/m/Y', strtotime($data['expiry_date'])) . '</p>
                    <a href="' . $data['app_url'] . '/vehicles/view/' . $data['vehicle']['id'] . '" class="button">View Vehicle</a>';
                break;
        }

        $html .= '</div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
}
