-- ============================================================================
-- MULTI-TENANT MIGRATION
-- Add company isolation to all tables
-- ============================================================================

-- 1. Create Companies Table
CREATE TABLE IF NOT EXISTS `companies` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(200) NOT NULL,
  `company_code` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Unique identifier for company',
  `legal_name` VARCHAR(200),
  `tax_id` VARCHAR(50) COMMENT 'Tax/VAT number',
  `registration_number` VARCHAR(50) COMMENT 'Company registration number',
  `industry` VARCHAR(100),
  `company_type` ENUM('fleet', 'taxi', 'delivery', 'logistics', 'transport', 'other') DEFAULT 'fleet',

  -- Contact Information
  `email` VARCHAR(100),
  `phone` VARCHAR(20),
  `website` VARCHAR(200),
  `address` TEXT,
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `postal_code` VARCHAR(20),
  `country` VARCHAR(100) DEFAULT 'Tunisia',

  -- Billing Information
  `billing_email` VARCHAR(100),
  `billing_address` TEXT,
  `billing_contact` VARCHAR(100),
  `billing_phone` VARCHAR(20),

  -- Settings
  `timezone` VARCHAR(50) DEFAULT 'Africa/Tunis',
  `currency` VARCHAR(10) DEFAULT 'TND',
  `language` VARCHAR(10) DEFAULT 'fr',
  `date_format` VARCHAR(20) DEFAULT 'd/m/Y',
  `time_format` VARCHAR(20) DEFAULT 'H:i',

  -- Subscription & Limits
  `subscription_status` ENUM('trial', 'active', 'suspended', 'cancelled', 'expired') DEFAULT 'trial',
  `trial_ends_at` DATE,
  `max_users` INT(11) DEFAULT 5,
  `max_vehicles` INT(11) DEFAULT 10,
  `max_drivers` INT(11) DEFAULT 10,

  -- Company Logo & Branding
  `logo` VARCHAR(255),
  `primary_color` VARCHAR(7) DEFAULT '#007bff',
  `secondary_color` VARCHAR(7) DEFAULT '#6c757d',

  -- Status & Metadata
  `status` ENUM('active', 'inactive', 'suspended', 'deleted') DEFAULT 'active',
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED COMMENT 'Admin who created this company',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_company_code` (`company_code`),
  KEY `idx_status` (`status`),
  KEY `idx_subscription_status` (`subscription_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add company_id to users table
ALTER TABLE `users`
  ADD COLUMN `company_id` INT(11) UNSIGNED NULL AFTER `id`,
  ADD COLUMN `is_super_admin` BOOLEAN DEFAULT FALSE COMMENT 'Platform super admin (not bound to company)' AFTER `role`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_users_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 3. Add company_id to vehicles table
ALTER TABLE `vehicles`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_vehicles_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- Make registration_number unique per company instead of globally
ALTER TABLE `vehicles` DROP INDEX `registration_number`;
ALTER TABLE `vehicles` ADD UNIQUE KEY `unique_registration_per_company` (`company_id`, `registration_number`);

-- 4. Add company_id to drivers table
ALTER TABLE `drivers`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_drivers_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 5. Add company_id to maintenance tables
ALTER TABLE `maintenance_schedules`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_maintenance_schedules_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `maintenance_records`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_maintenance_records_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `maintenance_tasks`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_maintenance_tasks_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 6. Add company_id to GPS tracking tables
ALTER TABLE `gps_devices`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_gps_devices_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `gps_tracking`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_gps_tracking_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `geofences`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_geofences_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 7. Add company_id to fuel management tables
ALTER TABLE `fuel_cards`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_fuel_cards_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `fuel_transactions`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_fuel_transactions_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `fuel_consumption`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_fuel_consumption_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `fuel_alerts`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_fuel_alerts_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 8. Add company_id to missions tables
ALTER TABLE `missions`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_missions_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `mission_items`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_mission_items_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `mission_billing`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_mission_billing_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `mission_updates`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_mission_updates_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 9. Add company_id to stock management tables (if exist)
ALTER TABLE `warehouses`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_warehouses_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `stock_items`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_stock_items_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `stock_movements`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_stock_movements_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 10. Add company_id to taxi/VTC tables (if exist)
ALTER TABLE `taxi_rides`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_taxi_rides_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `taxi_customers`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_taxi_customers_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 11. Add company_id to delivery/smart delivery tables (if exist)
ALTER TABLE `delivery_orders`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_delivery_orders_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `delivery_packages`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_delivery_packages_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

ALTER TABLE `delivery_routes`
  ADD COLUMN `company_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
  ADD KEY `idx_company` (`company_id`),
  ADD CONSTRAINT `fk_delivery_routes_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE;

-- 12. Create default demo company
INSERT INTO `companies` (
  `company_name`,
  `company_code`,
  `legal_name`,
  `email`,
  `phone`,
  `address`,
  `city`,
  `country`,
  `subscription_status`,
  `status`,
  `max_users`,
  `max_vehicles`,
  `max_drivers`
) VALUES (
  'Pakiparc Demo',
  'DEMO001',
  'Pakiparc Demo Company SARL',
  'demo@pakiparc.tn',
  '+216 71 123 456',
  'Avenue Habib Bourguiba',
  'Tunis',
  'Tunisia',
  'active',
  'active',
  50,
  100,
  100
);

-- Update existing users to belong to demo company (if any exist)
UPDATE `users` SET `company_id` = 1 WHERE `company_id` IS NULL;

-- Make company_id NOT NULL for users after assigning default company
ALTER TABLE `users` MODIFY `company_id` INT(11) UNSIGNED NOT NULL;

-- ============================================================================
-- VIEWS FOR MULTI-TENANT ACCESS
-- ============================================================================

-- View to get active company modules
CREATE OR REPLACE VIEW `v_company_active_modules` AS
SELECT
  cs.company_id,
  sm.module_code,
  sm.module_name,
  cs.status,
  cs.start_date,
  cs.end_date,
  cs.next_billing_date
FROM company_subscriptions cs
INNER JOIN subscription_modules sm ON cs.module_id = sm.id
WHERE cs.subscription_type = 'module'
  AND cs.status = 'active'
  AND (cs.end_date IS NULL OR cs.end_date >= CURDATE())

UNION

SELECT
  cs.company_id,
  sm.module_code,
  sm.module_name,
  cs.status,
  cs.start_date,
  cs.end_date,
  cs.next_billing_date
FROM company_subscriptions cs
INNER JOIN subscription_packs sp ON cs.pack_id = sp.id
INNER JOIN pack_modules pm ON sp.id = pm.pack_id
INNER JOIN subscription_modules sm ON pm.module_id = sm.id
WHERE cs.subscription_type = 'pack'
  AND cs.status = 'active'
  AND (cs.end_date IS NULL OR cs.end_date >= CURDATE());

-- ============================================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================================

-- Add composite indexes for common queries
CREATE INDEX idx_company_status ON vehicles(company_id, status);
CREATE INDEX idx_company_date ON fuel_transactions(company_id, transaction_date);
CREATE INDEX idx_company_mission_status ON missions(company_id, status);
CREATE INDEX idx_company_created ON missions(company_id, created_at);

-- ============================================================================
-- COMMENTS
-- ============================================================================

ALTER TABLE `companies` COMMENT = 'Multi-tenant companies/organizations table';
ALTER TABLE `users` MODIFY COLUMN `company_id` INT(11) UNSIGNED NOT NULL COMMENT 'Company this user belongs to';
