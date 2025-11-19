<?php

/**
 * Email Service
 * Handles email notifications via PHPMailer
 *
 * @author DigiParc Team
 * @version 1.0
 */
class EmailService
{
    private $mailer;

    public function __construct()
    {
        // PHPMailer would be required in production
        // require_once 'vendor/autoload.php';
        // $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        // $this->configure();
    }

    /**
     * Send email
     *
     * @param array $data
     * @return bool
     */
    public function send($data)
    {
        try {
            /*
            // In production with PHPMailer:
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($data['to']);
            $this->mailer->Subject = $data['subject'];
            $this->mailer->Body = $data['body'];
            $this->mailer->isHTML(true);

            return $this->mailer->send();
            */

            // For now, use PHP's mail() function
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: DigiParc <noreply@digiparc.com>\r\n";

            return mail($data['to'], $data['subject'], $data['body'], $headers);

        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Configure PHPMailer
     */
    private function configure()
    {
        /*
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USER;
        $this->mailer->Password = SMTP_PASS;
        $this->mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = SMTP_PORT;
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        */
    }
}
