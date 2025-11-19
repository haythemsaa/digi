-- Alerts System Tables
-- Migration file for intelligent alerts system

-- Alerts table
CREATE TABLE IF NOT EXISTS `alerts` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) UNSIGNED NOT NULL,
    `type` ENUM('maintenance', 'document_expiry', 'fuel_anomaly', 'stock_low', 'delivery_delay', 'geofence', 'inspection', 'other') NOT NULL,
    `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('unread', 'read', 'acknowledged', 'archived') NOT NULL DEFAULT 'unread',
    `related_type` VARCHAR(50) NULL COMMENT 'Type of related entity (vehicle, driver, mission, etc.)',
    `related_id` INT(11) NULL COMMENT 'ID of related entity',
    `action_url` VARCHAR(255) NULL COMMENT 'URL to resolve alert',
    `metadata` JSON NULL COMMENT 'Additional data specific to alert type',
    `expires_at` DATETIME NULL COMMENT 'When alert should be automatically archived',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_company_status` (`company_id`, `status`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_type` (`type`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_related` (`related_type`, `related_id`),
    CONSTRAINT `fk_alerts_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alert recipients (who should receive this alert)
CREATE TABLE IF NOT EXISTS `alert_recipients` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `alert_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `read_at` DATETIME NULL,
    `acknowledged_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_alert_user` (`alert_id`, `user_id`),
    INDEX `idx_user_read` (`user_id`, `read_at`),
    CONSTRAINT `fk_alert_recipients_alert` FOREIGN KEY (`alert_id`) REFERENCES `alerts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_alert_recipients_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification logs (track all notifications sent)
CREATE TABLE IF NOT EXISTS `notification_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NULL,
    `alert_id` INT(11) UNSIGNED NULL,
    `channel` ENUM('email', 'sms', 'push', 'whatsapp', 'internal') NOT NULL,
    `recipient` VARCHAR(255) NOT NULL COMMENT 'Email, phone number, etc.',
    `subject` VARCHAR(255) NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('pending', 'sent', 'failed', 'delivered', 'opened', 'clicked') NOT NULL DEFAULT 'pending',
    `error_message` TEXT NULL,
    `metadata` JSON NULL COMMENT 'Provider-specific data (message ID, etc.)',
    `sent_at` DATETIME NULL,
    `delivered_at` DATETIME NULL,
    `opened_at` DATETIME NULL,
    `clicked_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_company` (`company_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_alert` (`alert_id`),
    INDEX `idx_channel_status` (`channel`, `status`),
    INDEX `idx_created` (`created_at`),
    CONSTRAINT `fk_notification_logs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notification_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_notification_logs_alert` FOREIGN KEY (`alert_id`) REFERENCES `alerts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User notification preferences
CREATE TABLE IF NOT EXISTS `notification_preferences` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `alert_type` ENUM('maintenance', 'document_expiry', 'fuel_anomaly', 'stock_low', 'delivery_delay', 'geofence', 'inspection', 'other', 'all') NOT NULL DEFAULT 'all',
    `channel_email` TINYINT(1) NOT NULL DEFAULT 1,
    `channel_sms` TINYINT(1) NOT NULL DEFAULT 0,
    `channel_push` TINYINT(1) NOT NULL DEFAULT 1,
    `channel_whatsapp` TINYINT(1) NOT NULL DEFAULT 0,
    `channel_internal` TINYINT(1) NOT NULL DEFAULT 1,
    `priority_threshold` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium' COMMENT 'Only notify if priority >= threshold',
    `quiet_hours_start` TIME NULL COMMENT 'Start of quiet hours (no notifications)',
    `quiet_hours_end` TIME NULL COMMENT 'End of quiet hours',
    `enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_type` (`user_id`, `alert_type`),
    CONSTRAINT `fk_notification_prefs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alert rules (automatic alert generation)
CREATE TABLE IF NOT EXISTS `alert_rules` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `type` ENUM('maintenance', 'document_expiry', 'fuel_anomaly', 'stock_low', 'delivery_delay', 'geofence', 'inspection', 'other') NOT NULL,
    `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    `conditions` JSON NOT NULL COMMENT 'Rule conditions (e.g., {field: "mileage", operator: ">=", value: 10000})',
    `action_template` JSON NOT NULL COMMENT 'Alert template (title, message)',
    `enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `check_frequency` INT(11) NOT NULL DEFAULT 3600 COMMENT 'How often to check rule (seconds)',
    `last_checked_at` DATETIME NULL,
    `created_by` INT(11) UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_company_enabled` (`company_id`, `enabled`),
    INDEX `idx_type` (`type`),
    CONSTRAINT `fk_alert_rules_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_alert_rules_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default alert rules for maintenance
INSERT INTO `alert_rules` (`company_id`, `name`, `description`, `type`, `priority`, `conditions`, `action_template`, `check_frequency`) VALUES
(1, 'Maintenance Préventive - 10000 km', 'Alerte quand un véhicule atteint 10000 km depuis dernière maintenance', 'maintenance', 'medium',
'{"field": "km_since_maintenance", "operator": ">=", "value": 10000}',
'{"title": "Maintenance Préventive Requise", "message": "Le véhicule {{vehicle}} a parcouru {{km}} km depuis sa dernière maintenance."}',
86400),

(1, 'Expiration Assurance - 30 jours', 'Alerte 30 jours avant expiration assurance', 'document_expiry', 'high',
'{"field": "insurance_expiry", "operator": "<=", "value": 30, "unit": "days"}',
'{"title": "Expiration Assurance Imminente", "message": "L\'assurance du véhicule {{vehicle}} expire le {{expiry_date}}."}',
86400),

(1, 'Stock Minimum Atteint', 'Alerte quand stock produit atteint niveau minimum', 'stock_low', 'medium',
'{"field": "stock_quantity", "operator": "<=", "value": "minimum_stock"}',
'{"title": "Stock Minimum Atteint", "message": "Le produit {{product}} a atteint le stock minimum ({{quantity}} unités)."}',
3600);

-- Sample data for testing
INSERT INTO `alerts` (`company_id`, `type`, `priority`, `title`, `message`, `related_type`, `related_id`) VALUES
(1, 'maintenance', 'high', 'Maintenance Préventive Requise', 'Le véhicule ABC-123 a parcouru 10500 km depuis sa dernière maintenance.', 'vehicle', 1),
(1, 'document_expiry', 'critical', 'Assurance Expire Dans 7 Jours', 'L\'assurance du véhicule XYZ-789 expire le 26/11/2025.', 'vehicle', 2),
(1, 'fuel_anomaly', 'medium', 'Consommation Anormale Détectée', 'Le véhicule DEF-456 a une consommation 20% supérieure à la normale.', 'vehicle', 3);
