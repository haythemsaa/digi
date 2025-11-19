<?php

/**
 * GDPR Service
 * Handles GDPR/RGPD compliance features
 *
 * @author Pakiparc Team
 * @version 1.0
 */
class GDPRService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create GDPR request
     */
    public function createRequest($userId, $type, $details = null)
    {
        $companyId = getCurrentCompanyId();

        $sql = "INSERT INTO gdpr_requests (company_id, user_id, request_type, request_details)
                VALUES (:company_id, :user_id, :request_type, :details)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':request_type', $type);
        $stmt->bindParam(':details', $details);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Export all user data (GDPR Article 20 - Data Portability)
     */
    public function exportUserData($userId)
    {
        $companyId = getCurrentCompanyId();
        $data = [];

        // User basic info
        $sql = "SELECT * FROM users WHERE id = :user_id AND company_id = :company_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $data['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Remove sensitive fields
        unset($data['user']['password']);

        // Missions as driver
        $sql = "SELECT * FROM missions WHERE driver_id = :user_id AND company_id = :company_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $data['missions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // GPS tracking data
        $sql = "SELECT * FROM tracking WHERE user_id = :user_id AND company_id = :company_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $data['tracking'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Notifications
        $sql = "SELECT * FROM notification_logs WHERE user_id = :user_id AND company_id = :company_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $data['notifications'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consents
        $sql = "SELECT * FROM user_consents WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $data['consents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Access logs
        $sql = "SELECT * FROM data_access_logs WHERE user_id = :user_id AND company_id = :company_id ORDER BY accessed_at DESC LIMIT 1000";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $data['access_logs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * Delete all user data (GDPR Article 17 - Right to Erasure)
     */
    public function deleteUserData($userId)
    {
        $companyId = getCurrentCompanyId();

        try {
            $this->db->beginTransaction();

            // Anonymize missions (keep for business records but remove personal data)
            $sql = "UPDATE missions SET driver_id = NULL WHERE driver_id = :user_id AND company_id = :company_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            // Delete tracking data
            $sql = "DELETE FROM tracking WHERE user_id = :user_id AND company_id = :company_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            // Delete notifications
            $sql = "DELETE FROM notification_logs WHERE user_id = :user_id AND company_id = :company_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            // Delete consents
            $sql = "DELETE FROM user_consents WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            // Anonymize user record (keep for audit but remove personal data)
            $sql = "UPDATE users SET
                    first_name = 'Deleted',
                    last_name = 'User',
                    email = CONCAT('deleted_', id, '@anonymized.local'),
                    phone = NULL,
                    address = NULL,
                    status = 'deleted',
                    deleted_at = NOW()
                    WHERE id = :user_id AND company_id = :company_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':company_id', $companyId);
            $stmt->execute();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("GDPR deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record user consent
     */
    public function recordConsent($userId, $type, $given, $text)
    {
        $sql = "INSERT INTO user_consents (user_id, consent_type, consent_given, consent_text, consent_date, ip_address, user_agent)
                VALUES (:user_id, :type, :given, :text, NOW(), :ip, :ua)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':given', $given, PDO::PARAM_INT);
        $stmt->bindParam(':text', $text);

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':ua', $ua);

        return $stmt->execute();
    }

    /**
     * Withdraw consent
     */
    public function withdrawConsent($userId, $type)
    {
        $sql = "UPDATE user_consents
                SET consent_given = 0, withdrawal_date = NOW()
                WHERE user_id = :user_id AND consent_type = :type AND consent_given = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);

        return $stmt->execute();
    }

    /**
     * Check if user has given consent
     */
    public function hasConsent($userId, $type)
    {
        $sql = "SELECT consent_given FROM user_consents
                WHERE user_id = :user_id AND consent_type = :type
                ORDER BY consent_date DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (bool)$result['consent_given'] : false;
    }

    /**
     * Log data access (for audit trail)
     */
    public function logDataAccess($tableName, $recordId, $action)
    {
        $companyId = getCurrentCompanyId();
        $userId = $_SESSION['user_id'] ?? null;

        $sql = "INSERT INTO data_access_logs (company_id, user_id, table_name, record_id, action, ip_address, user_agent)
                VALUES (:company_id, :user_id, :table, :record_id, :action, :ip, :ua)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':table', $tableName);
        $stmt->bindParam(':record_id', $recordId);
        $stmt->bindParam(':action', $action);

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':ua', $ua);

        try {
            $stmt->execute();
        } catch (Exception $e) {
            // Don't fail the main operation if logging fails
            error_log("Data access logging error: " . $e->getMessage());
        }
    }

    /**
     * Get processing registry
     */
    public function getProcessingRegistry($companyId)
    {
        $sql = "SELECT * FROM data_processing_registry
                WHERE company_id = :company_id AND is_active = 1
                ORDER BY processing_name";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate GDPR compliance report
     */
    public function generateComplianceReport($companyId)
    {
        $report = [];

        // Count GDPR requests
        $sql = "SELECT request_type, status, COUNT(*) as count
                FROM gdpr_requests
                WHERE company_id = :company_id
                GROUP BY request_type, status";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $report['requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Processing registry
        $report['processing_activities'] = $this->getProcessingRegistry($companyId);

        // Consent statistics
        $sql = "SELECT consent_type,
                       SUM(CASE WHEN consent_given = 1 THEN 1 ELSE 0 END) as given,
                       SUM(CASE WHEN consent_given = 0 THEN 1 ELSE 0 END) as withdrawn
                FROM user_consents uc
                JOIN users u ON uc.user_id = u.id
                WHERE u.company_id = :company_id
                GROUP BY consent_type";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $report['consents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Data breaches
        $sql = "SELECT * FROM data_breaches
                WHERE company_id = :company_id
                ORDER BY discovered_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $report['breaches'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $report;
    }

    /**
     * Anonymize old data (for data retention compliance)
     */
    public function anonymizeOldData($tableName, $retentionDays = 365)
    {
        // This should be customized per table
        // Example for tracking data older than retention period

        if ($tableName === 'tracking') {
            $sql = "UPDATE tracking
                    SET latitude = 0, longitude = 0, address = 'Anonymized'
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                    AND latitude != 0";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':days', $retentionDays, PDO::PARAM_INT);
            return $stmt->execute();
        }

        return false;
    }
}
