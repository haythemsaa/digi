-- GDPR Compliance Tables
-- Migration for GDPR/RGPD compliance features

-- GDPR requests (data export, deletion, etc.)
CREATE TABLE IF NOT EXISTS `gdpr_requests` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `request_type` ENUM('data_export', 'data_deletion', 'data_rectification', 'access_request') NOT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
    `request_details` TEXT NULL,
    `response_data` LONGTEXT NULL COMMENT 'Exported data or response',
    `processed_by` INT(11) UNSIGNED NULL,
    `processed_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_company` (`company_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_gdpr_requests_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_gdpr_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_gdpr_requests_processor` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data processing registry (Article 30 GDPR)
CREATE TABLE IF NOT EXISTS `data_processing_registry` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) UNSIGNED NOT NULL,
    `processing_name` VARCHAR(255) NOT NULL,
    `processing_purpose` TEXT NOT NULL,
    `data_categories` JSON NOT NULL COMMENT 'Types of personal data processed',
    `data_subjects` JSON NOT NULL COMMENT 'Categories of data subjects',
    `recipients` TEXT NULL COMMENT 'Who receives the data',
    `third_country_transfers` TEXT NULL,
    `retention_period` VARCHAR(255) NOT NULL,
    `security_measures` TEXT NOT NULL,
    `legal_basis` ENUM('consent', 'contract', 'legal_obligation', 'vital_interests', 'public_interest', 'legitimate_interests') NOT NULL,
    `dpo_contact` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT(11) UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_company` (`company_id`),
    CONSTRAINT `fk_processing_registry_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_processing_registry_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Consent management
CREATE TABLE IF NOT EXISTS `user_consents` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `consent_type` ENUM('cookies', 'marketing', 'analytics', 'third_party', 'profiling') NOT NULL,
    `consent_given` TINYINT(1) NOT NULL DEFAULT 0,
    `consent_text` TEXT NOT NULL COMMENT 'Text shown to user when consent requested',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `consent_date` DATETIME NOT NULL,
    `withdrawal_date` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_type` (`user_id`, `consent_type`),
    INDEX `idx_consent_date` (`consent_date`),
    CONSTRAINT `fk_consents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data breach incidents (Article 33 GDPR)
CREATE TABLE IF NOT EXISTS `data_breaches` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) UNSIGNED NOT NULL,
    `breach_date` DATETIME NOT NULL,
    `discovered_date` DATETIME NOT NULL,
    `breach_type` ENUM('confidentiality', 'integrity', 'availability') NOT NULL,
    `affected_data` TEXT NOT NULL,
    `affected_users_count` INT(11) NOT NULL DEFAULT 0,
    `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    `description` TEXT NOT NULL,
    `containment_measures` TEXT NULL,
    `notification_required` TINYINT(1) NOT NULL DEFAULT 0,
    `authority_notified` TINYINT(1) NOT NULL DEFAULT 0,
    `authority_notification_date` DATETIME NULL,
    `users_notified` TINYINT(1) NOT NULL DEFAULT 0,
    `users_notification_date` DATETIME NULL,
    `status` ENUM('investigating', 'contained', 'resolved', 'ongoing') NOT NULL DEFAULT 'investigating',
    `reported_by` INT(11) UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_company` (`company_id`),
    INDEX `idx_severity` (`severity`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_breaches_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_breaches_reporter` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data access logs (for audit trail)
CREATE TABLE IF NOT EXISTS `data_access_logs` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NULL,
    `table_name` VARCHAR(64) NOT NULL,
    `record_id` INT(11) NOT NULL,
    `action` ENUM('view', 'create', 'update', 'delete', 'export') NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `accessed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_company` (`company_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_table_record` (`table_name`, `record_id`),
    INDEX `idx_accessed` (`accessed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data processing registry
INSERT INTO `data_processing_registry` (`company_id`, `processing_name`, `processing_purpose`, `data_categories`, `data_subjects`, `recipients`, `retention_period`, `security_measures`, `legal_basis`) VALUES
(1, 'Gestion des utilisateurs', 'Gestion des comptes utilisateurs et authentification',
'["nom", "prénom", "email", "mot de passe", "téléphone"]',
'["employés", "gestionnaires", "administrateurs"]',
'Données internes uniquement',
'Durée du contrat + 5 ans',
'Chiffrement des mots de passe (bcrypt), HTTPS, contrôle d\'accès basé sur les rôles',
'contract'),

(1, 'Gestion de flotte', 'Suivi des véhicules et conducteurs pour gestion opérationnelle',
'["nom conducteur", "permis", "données GPS", "consommation"]',
'["conducteurs", "gestionnaires de flotte"]',
'Prestataires GPS (si applicable)',
'Durée du contrat + 3 ans',
'Anonymisation des données GPS après 90 jours, accès restreint',
'legitimate_interests');
