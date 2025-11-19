-- DigiParc Fleet Management System - Database Schema
-- Complete database structure for all modules

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================================
-- USER MANAGEMENT & AUTHENTICATION
-- ============================================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(20),
  `role` ENUM('admin', 'manager', 'dispatcher', 'driver', 'mechanic', 'accountant') NOT NULL DEFAULT 'driver',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `avatar` VARCHAR(255),
  `last_login` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(128) NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255),
  `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- FLEET MANAGEMENT
-- ============================================================================

CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_number` VARCHAR(20) NOT NULL UNIQUE,
  `vin` VARCHAR(17) UNIQUE COMMENT 'Vehicle Identification Number',
  `brand` VARCHAR(50) NOT NULL,
  `model` VARCHAR(50) NOT NULL,
  `year` INT(4),
  `color` VARCHAR(30),
  `type` ENUM('car', 'truck', 'van', 'bus', 'motorcycle', 'trailer', 'special') NOT NULL,
  `fuel_type` ENUM('gasoline', 'diesel', 'electric', 'hybrid', 'lpg', 'cng') NOT NULL,
  `engine_capacity` DECIMAL(4,2) COMMENT 'in liters',
  `power` INT(5) COMMENT 'in HP',
  `transmission` ENUM('manual', 'automatic', 'semi-automatic'),
  `seats` INT(3),
  `doors` INT(2),
  `weight` DECIMAL(8,2) COMMENT 'in kg',
  `load_capacity` DECIMAL(8,2) COMMENT 'in kg',
  `purchase_date` DATE,
  `purchase_price` DECIMAL(12,2),
  `current_value` DECIMAL(12,2),
  `insurance_company` VARCHAR(100),
  `insurance_policy` VARCHAR(50),
  `insurance_expiry` DATE,
  `registration_expiry` DATE,
  `technical_control_expiry` DATE,
  `odometer` INT(11) DEFAULT 0 COMMENT 'in km',
  `fuel_tank_capacity` DECIMAL(6,2) COMMENT 'in liters',
  `status` ENUM('active', 'maintenance', 'repair', 'inactive', 'sold', 'accident') NOT NULL DEFAULT 'active',
  `gps_device_id` VARCHAR(50),
  `notes` TEXT,
  `photo` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_registration` (`registration_number`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vehicle_documents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `document_type` ENUM('registration', 'insurance', 'technical_control', 'contract', 'invoice', 'other') NOT NULL,
  `document_name` VARCHAR(100) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `expiry_date` DATE,
  `uploaded_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vehicle_assignments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `driver_id` INT(11) UNSIGNED NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME,
  `odometer_start` INT(11),
  `odometer_end` INT(11),
  `status` ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `fuel_entries` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `driver_id` INT(11) UNSIGNED,
  `date` DATETIME NOT NULL,
  `odometer` INT(11) NOT NULL,
  `fuel_type` ENUM('gasoline', 'diesel', 'electric', 'hybrid', 'lpg', 'cng') NOT NULL,
  `quantity` DECIMAL(8,2) NOT NULL COMMENT 'in liters',
  `price_per_unit` DECIMAL(8,2) NOT NULL,
  `total_cost` DECIMAL(10,2) NOT NULL,
  `station_name` VARCHAR(100),
  `is_full_tank` BOOLEAN DEFAULT TRUE,
  `receipt_number` VARCHAR(50),
  `receipt_photo` VARCHAR(255),
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- GPS & TELEMATICS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `gps_devices` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `device_id` VARCHAR(50) NOT NULL UNIQUE,
  `imei` VARCHAR(20) UNIQUE,
  `sim_number` VARCHAR(20),
  `model` VARCHAR(50),
  `provider` VARCHAR(50),
  `status` ENUM('active', 'inactive', 'maintenance') NOT NULL DEFAULT 'active',
  `installation_date` DATE,
  `last_communication` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `gps_positions` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `latitude` DECIMAL(10,8) NOT NULL,
  `longitude` DECIMAL(11,8) NOT NULL,
  `altitude` DECIMAL(8,2),
  `speed` DECIMAL(6,2) COMMENT 'in km/h',
  `heading` DECIMAL(5,2) COMMENT 'degrees',
  `accuracy` DECIMAL(6,2) COMMENT 'in meters',
  `satellites` INT(2),
  `odometer` INT(11),
  `fuel_level` DECIMAL(5,2) COMMENT 'percentage',
  `engine_status` BOOLEAN DEFAULT FALSE,
  `timestamp` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  KEY `idx_vehicle_timestamp` (`vehicle_id`, `timestamp`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `geofences` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('circle', 'polygon') NOT NULL,
  `coordinates` TEXT NOT NULL COMMENT 'JSON format',
  `radius` DECIMAL(10,2) COMMENT 'in meters for circle type',
  `color` VARCHAR(7) DEFAULT '#FF0000',
  `description` TEXT,
  `active` BOOLEAN DEFAULT TRUE,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `geofence_alerts` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `geofence_id` INT(11) UNSIGNED NOT NULL,
  `alert_type` ENUM('entry', 'exit') NOT NULL,
  `latitude` DECIMAL(10,8) NOT NULL,
  `longitude` DECIMAL(11,8) NOT NULL,
  `timestamp` DATETIME NOT NULL,
  `acknowledged` BOOLEAN DEFAULT FALSE,
  `acknowledged_by` INT(11) UNSIGNED,
  `acknowledged_at` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`geofence_id`) REFERENCES `geofences`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`acknowledged_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `speed_alerts` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `driver_id` INT(11) UNSIGNED,
  `speed` DECIMAL(6,2) NOT NULL,
  `speed_limit` DECIMAL(6,2) NOT NULL,
  `latitude` DECIMAL(10,8) NOT NULL,
  `longitude` DECIMAL(11,8) NOT NULL,
  `timestamp` DATETIME NOT NULL,
  `acknowledged` BOOLEAN DEFAULT FALSE,
  `acknowledged_by` INT(11) UNSIGNED,
  `acknowledged_at` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`acknowledged_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `trips` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `driver_id` INT(11) UNSIGNED,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME,
  `start_location` VARCHAR(255),
  `start_latitude` DECIMAL(10,8),
  `start_longitude` DECIMAL(11,8),
  `end_location` VARCHAR(255),
  `end_latitude` DECIMAL(10,8),
  `end_longitude` DECIMAL(11,8),
  `distance` DECIMAL(10,2) COMMENT 'in km',
  `duration` INT(11) COMMENT 'in seconds',
  `max_speed` DECIMAL(6,2),
  `avg_speed` DECIMAL(6,2),
  `fuel_consumed` DECIMAL(8,2),
  `idle_time` INT(11) COMMENT 'in seconds',
  `status` ENUM('ongoing', 'completed', 'interrupted') NOT NULL DEFAULT 'ongoing',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- MAINTENANCE MANAGEMENT (GMAO)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `maintenance_types` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `category` ENUM('preventive', 'corrective', 'inspection', 'tire', 'bodywork', 'other') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `maintenance_schedules` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `maintenance_type_id` INT(11) UNSIGNED NOT NULL,
  `schedule_type` ENUM('km', 'time', 'both') NOT NULL,
  `interval_km` INT(11) COMMENT 'interval in km',
  `interval_days` INT(11) COMMENT 'interval in days',
  `last_service_km` INT(11),
  `last_service_date` DATE,
  `next_service_km` INT(11),
  `next_service_date` DATE,
  `active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`maintenance_type_id`) REFERENCES `maintenance_types`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `work_orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference` VARCHAR(50) NOT NULL UNIQUE,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `maintenance_type_id` INT(11) UNSIGNED,
  `type` ENUM('preventive', 'corrective', 'inspection', 'emergency') NOT NULL,
  `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
  `status` ENUM('pending', 'scheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `description` TEXT NOT NULL,
  `reported_by` INT(11) UNSIGNED NOT NULL,
  `assigned_to` INT(11) UNSIGNED COMMENT 'Mechanic',
  `scheduled_date` DATETIME,
  `start_date` DATETIME,
  `completion_date` DATETIME,
  `odometer_at_service` INT(11),
  `labor_cost` DECIMAL(10,2) DEFAULT 0,
  `parts_cost` DECIMAL(10,2) DEFAULT 0,
  `total_cost` DECIMAL(10,2) DEFAULT 0,
  `workshop` VARCHAR(100),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`maintenance_type_id`) REFERENCES `maintenance_types`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`reported_by`) REFERENCES `users`(`id`),
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `work_order_parts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `work_order_id` INT(11) UNSIGNED NOT NULL,
  `part_id` INT(11) UNSIGNED,
  `part_name` VARCHAR(100) NOT NULL,
  `part_reference` VARCHAR(50),
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `supplier_id` INT(11) UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`work_order_id`) REFERENCES `work_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tire_management` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `position` ENUM('front_left', 'front_right', 'rear_left', 'rear_right', 'spare') NOT NULL,
  `brand` VARCHAR(50),
  `model` VARCHAR(50),
  `size` VARCHAR(20),
  `serial_number` VARCHAR(50),
  `installation_date` DATE,
  `installation_km` INT(11),
  `tread_depth` DECIMAL(4,2) COMMENT 'in mm',
  `pressure` DECIMAL(4,2) COMMENT 'in bar',
  `status` ENUM('active', 'rotated', 'replaced', 'damaged') NOT NULL DEFAULT 'active',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- TRANSPORT MANAGEMENT SYSTEM (TMS)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_type` ENUM('individual', 'company') NOT NULL,
  `company_name` VARCHAR(100),
  `first_name` VARCHAR(50),
  `last_name` VARCHAR(50),
  `email` VARCHAR(100),
  `phone` VARCHAR(20),
  `mobile` VARCHAR(20),
  `tax_id` VARCHAR(50),
  `address` VARCHAR(255),
  `city` VARCHAR(50),
  `postal_code` VARCHAR(10),
  `country` VARCHAR(50),
  `payment_terms` INT(3) DEFAULT 30 COMMENT 'days',
  `credit_limit` DECIMAL(12,2),
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_company_name` (`company_name`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transport_quotes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quote_number` VARCHAR(50) NOT NULL UNIQUE,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `pickup_address` VARCHAR(255) NOT NULL,
  `pickup_city` VARCHAR(50) NOT NULL,
  `pickup_date` DATETIME NOT NULL,
  `delivery_address` VARCHAR(255) NOT NULL,
  `delivery_city` VARCHAR(50) NOT NULL,
  `delivery_date` DATETIME,
  `distance` DECIMAL(10,2) COMMENT 'in km',
  `cargo_type` VARCHAR(100),
  `cargo_weight` DECIMAL(10,2) COMMENT 'in kg',
  `cargo_volume` DECIMAL(10,2) COMMENT 'in m3',
  `vehicle_type_required` VARCHAR(50),
  `price` DECIMAL(12,2) NOT NULL,
  `tax_rate` DECIMAL(5,2) DEFAULT 0,
  `tax_amount` DECIMAL(12,2) DEFAULT 0,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('draft', 'sent', 'accepted', 'rejected', 'expired', 'converted') NOT NULL DEFAULT 'draft',
  `valid_until` DATE,
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_quote_number` (`quote_number`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transport_orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `quote_id` INT(11) UNSIGNED,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `vehicle_id` INT(11) UNSIGNED,
  `driver_id` INT(11) UNSIGNED,
  `pickup_address` VARCHAR(255) NOT NULL,
  `pickup_city` VARCHAR(50) NOT NULL,
  `pickup_date` DATETIME NOT NULL,
  `pickup_contact` VARCHAR(100),
  `pickup_phone` VARCHAR(20),
  `delivery_address` VARCHAR(255) NOT NULL,
  `delivery_city` VARCHAR(50) NOT NULL,
  `delivery_date` DATETIME,
  `delivery_contact` VARCHAR(100),
  `delivery_phone` VARCHAR(20),
  `distance` DECIMAL(10,2) COMMENT 'in km',
  `cargo_type` VARCHAR(100),
  `cargo_description` TEXT,
  `cargo_weight` DECIMAL(10,2) COMMENT 'in kg',
  `cargo_volume` DECIMAL(10,2) COMMENT 'in m3',
  `price` DECIMAL(12,2) NOT NULL,
  `tax_rate` DECIMAL(5,2) DEFAULT 0,
  `tax_amount` DECIMAL(12,2) DEFAULT 0,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'assigned', 'in_transit', 'delivered', 'cancelled', 'invoiced') NOT NULL DEFAULT 'pending',
  `priority` ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
  `special_instructions` TEXT,
  `proof_of_delivery` VARCHAR(255),
  `signature` VARCHAR(255),
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`quote_id`) REFERENCES `transport_quotes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `transport_order_id` INT(11) UNSIGNED,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `tax_rate` DECIMAL(5,2) DEFAULT 0,
  `tax_amount` DECIMAL(12,2) DEFAULT 0,
  `discount` DECIMAL(12,2) DEFAULT 0,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `paid_amount` DECIMAL(12,2) DEFAULT 0,
  `balance` DECIMAL(12,2) NOT NULL,
  `status` ENUM('draft', 'sent', 'paid', 'partially_paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'draft',
  `payment_method` ENUM('cash', 'check', 'transfer', 'card', 'other'),
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`transport_order_id`) REFERENCES `transport_orders`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_invoice_number` (`invoice_number`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- HR MANAGEMENT (DRIVERS & PERSONNEL)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `driver_profiles` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `license_number` VARCHAR(50) NOT NULL,
  `license_type` VARCHAR(20) COMMENT 'A, B, C, D, E, etc.',
  `license_issue_date` DATE,
  `license_expiry_date` DATE,
  `medical_certificate_expiry` DATE,
  `emergency_contact_name` VARCHAR(100),
  `emergency_contact_phone` VARCHAR(20),
  `blood_type` VARCHAR(5),
  `hire_date` DATE,
  `contract_type` ENUM('permanent', 'temporary', 'freelance') NOT NULL,
  `salary` DECIMAL(10,2),
  `bank_account` VARCHAR(50),
  `social_security_number` VARCHAR(50),
  `status` ENUM('active', 'on_leave', 'suspended', 'terminated') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY (`license_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `driver_infractions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` INT(11) UNSIGNED NOT NULL,
  `vehicle_id` INT(11) UNSIGNED,
  `infraction_date` DATE NOT NULL,
  `infraction_type` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `location` VARCHAR(255),
  `fine_amount` DECIMAL(10,2),
  `points_deducted` INT(2),
  `paid` BOOLEAN DEFAULT FALSE,
  `paid_date` DATE,
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `driver_medical_exams` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` INT(11) UNSIGNED NOT NULL,
  `exam_date` DATE NOT NULL,
  `exam_type` ENUM('periodic', 'pre_employment', 'post_accident', 'other') NOT NULL,
  `doctor_name` VARCHAR(100),
  `clinic` VARCHAR(100),
  `result` ENUM('fit', 'fit_with_restrictions', 'unfit', 'pending') NOT NULL,
  `restrictions` TEXT,
  `next_exam_date` DATE,
  `certificate_file` VARCHAR(255),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `driver_training` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `driver_id` INT(11) UNSIGNED NOT NULL,
  `training_type` VARCHAR(100) NOT NULL,
  `training_provider` VARCHAR(100),
  `start_date` DATE NOT NULL,
  `end_date` DATE,
  `duration_hours` INT(4),
  `cost` DECIMAL(10,2),
  `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
  `certificate_file` VARCHAR(255),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- FINANCIAL MANAGEMENT
-- ============================================================================

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_number` VARCHAR(50) NOT NULL UNIQUE,
  `account_name` VARCHAR(100) NOT NULL,
  `account_type` ENUM('bank', 'cash', 'credit_card', 'other') NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'TND',
  `balance` DECIMAL(15,2) DEFAULT 0,
  `bank_name` VARCHAR(100),
  `iban` VARCHAR(50),
  `swift` VARCHAR(20),
  `status` ENUM('active', 'inactive', 'closed') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_date` DATE NOT NULL,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `type` ENUM('income', 'expense', 'transfer') NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `description` TEXT,
  `reference` VARCHAR(50),
  `invoice_id` INT(11) UNSIGNED,
  `work_order_id` INT(11) UNSIGNED,
  `payment_method` ENUM('cash', 'check', 'transfer', 'card', 'other'),
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`work_order_id`) REFERENCES `work_orders`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_date` (`transaction_date`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- PROCUREMENT & SUPPLIERS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(100) NOT NULL,
  `contact_person` VARCHAR(100),
  `email` VARCHAR(100),
  `phone` VARCHAR(20),
  `mobile` VARCHAR(20),
  `tax_id` VARCHAR(50),
  `address` VARCHAR(255),
  `city` VARCHAR(50),
  `postal_code` VARCHAR(10),
  `country` VARCHAR(50),
  `payment_terms` INT(3) DEFAULT 30 COMMENT 'days',
  `category` ENUM('parts', 'fuel', 'tires', 'services', 'other') NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `purchase_orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `po_number` VARCHAR(50) NOT NULL UNIQUE,
  `supplier_id` INT(11) UNSIGNED NOT NULL,
  `order_date` DATE NOT NULL,
  `expected_delivery_date` DATE,
  `delivery_date` DATE,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `tax_rate` DECIMAL(5,2) DEFAULT 0,
  `tax_amount` DECIMAL(12,2) DEFAULT 0,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('draft', 'sent', 'confirmed', 'received', 'cancelled') NOT NULL DEFAULT 'draft',
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_po_number` (`po_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- INVENTORY & STOCK MANAGEMENT
-- ============================================================================

CREATE TABLE IF NOT EXISTS `parts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `part_number` VARCHAR(50) NOT NULL UNIQUE,
  `part_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `category` ENUM('engine', 'transmission', 'brakes', 'suspension', 'electrical', 'body', 'tire', 'fluid', 'other') NOT NULL,
  `unit` VARCHAR(20) DEFAULT 'piece',
  `min_stock` INT(11) DEFAULT 0,
  `current_stock` INT(11) DEFAULT 0,
  `unit_cost` DECIMAL(10,2),
  `selling_price` DECIMAL(10,2),
  `location` VARCHAR(50),
  `supplier_id` INT(11) UNSIGNED,
  `photo` VARCHAR(255),
  `status` ENUM('active', 'discontinued') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
  KEY `idx_part_number` (`part_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `part_id` INT(11) UNSIGNED NOT NULL,
  `movement_type` ENUM('in', 'out', 'adjustment') NOT NULL,
  `quantity` INT(11) NOT NULL,
  `reference_type` ENUM('purchase_order', 'work_order', 'return', 'adjustment', 'other'),
  `reference_id` INT(11) UNSIGNED,
  `unit_cost` DECIMAL(10,2),
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`part_id`) REFERENCES `parts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
  KEY `idx_movement_type` (`movement_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- NOTIFICATIONS & ALERTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `type` ENUM('alert', 'reminder', 'info', 'warning', 'error') NOT NULL,
  `category` ENUM('gps', 'maintenance', 'document', 'finance', 'transport', 'system') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255),
  `read` BOOLEAN DEFAULT FALSE,
  `read_at` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `idx_user_read` (`user_id`, `read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- SYSTEM SETTINGS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT,
  `setting_type` ENUM('string', 'number', 'boolean', 'json') NOT NULL DEFAULT 'string',
  `description` TEXT,
  `updated_by` INT(11) UNSIGNED,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- AUDIT LOG
-- ============================================================================

CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED,
  `action` VARCHAR(50) NOT NULL,
  `table_name` VARCHAR(50) NOT NULL,
  `record_id` INT(11) UNSIGNED,
  `old_values` TEXT COMMENT 'JSON format',
  `new_values` TEXT COMMENT 'JSON format',
  `ip_address` VARCHAR(45),
  `user_agent` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_table_record` (`table_name`, `record_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- INSERT DEFAULT DATA
-- ============================================================================

-- Default admin user (password: admin123 - hashed with bcrypt)
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES
('admin', 'admin@digiparc.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'DigiParc', 'admin', 'active');

-- Default maintenance types
INSERT INTO `maintenance_types` (`name`, `description`, `category`) VALUES
('Oil Change', 'Engine oil and filter replacement', 'preventive'),
('Tire Rotation', 'Rotate tires for even wear', 'tire'),
('Brake Inspection', 'Check brake pads, discs, and fluid', 'preventive'),
('General Inspection', 'Complete vehicle inspection', 'inspection'),
('Battery Check', 'Battery voltage and connections check', 'preventive'),
('Air Filter Replacement', 'Replace engine air filter', 'preventive'),
('Coolant Flush', 'Cooling system flush and refill', 'preventive');

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('company_name', 'DigiParc Fleet Management', 'string', 'Company name'),
('currency', 'TND', 'string', 'Default currency'),
('date_format', 'Y-m-d', 'string', 'Date format'),
('timezone', 'Africa/Tunis', 'string', 'Default timezone'),
('items_per_page', '20', 'number', 'Pagination items per page'),
('maintenance_alert_days', '7', 'number', 'Days before maintenance to send alert'),
('speed_limit_default', '120', 'number', 'Default speed limit in km/h');

-- ============================================================================
-- VEHICLE RENTAL/LOCATION MODULE
-- ============================================================================

-- Rental rates (tarification location)
CREATE TABLE IF NOT EXISTS `rental_rates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_type` VARCHAR(50) NOT NULL,
  `rate_type` ENUM('hourly', 'daily', 'weekly', 'monthly') NOT NULL DEFAULT 'daily',
  `rate` DECIMAL(10,2) NOT NULL,
  `deposit_amount` DECIMAL(10,2) DEFAULT 0,
  `mileage_limit` INT(11) COMMENT 'KM limit per period',
  `excess_km_rate` DECIMAL(10,2) COMMENT 'Rate per excess KM',
  `insurance_included` BOOLEAN DEFAULT FALSE,
  `fuel_policy` ENUM('full_full', 'full_empty', 'empty_empty') DEFAULT 'full_full',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vehicle_type` (`vehicle_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rental contracts (contrats de location)
CREATE TABLE IF NOT EXISTS `rental_contracts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_number` VARCHAR(50) UNIQUE NOT NULL,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `rate_id` INT(11) UNSIGNED,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `start_time` TIME,
  `end_time` TIME,
  `pickup_location` VARCHAR(255),
  `return_location` VARCHAR(255),
  `start_mileage` INT(11),
  `end_mileage` INT(11),
  `fuel_level_start` DECIMAL(5,2) COMMENT 'Percentage 0-100',
  `fuel_level_end` DECIMAL(5,2) COMMENT 'Percentage 0-100',
  `daily_rate` DECIMAL(10,2) NOT NULL,
  `total_days` INT(11) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `deposit` DECIMAL(10,2) DEFAULT 0,
  `insurance_cost` DECIMAL(10,2) DEFAULT 0,
  `additional_charges` DECIMAL(10,2) DEFAULT 0,
  `discount` DECIMAL(10,2) DEFAULT 0,
  `tax_rate` DECIMAL(5,2) DEFAULT 19.00,
  `tax_amount` DECIMAL(10,2) DEFAULT 0,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_status` ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending',
  `status` ENUM('reserved', 'active', 'completed', 'cancelled') DEFAULT 'reserved',
  `driver_name` VARCHAR(100),
  `driver_license` VARCHAR(50),
  `driver_phone` VARCHAR(20),
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`rate_id`) REFERENCES `rental_rates`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_contract_number` (`contract_number`),
  KEY `idx_dates` (`start_date`, `end_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rental inspections (états des lieux)
CREATE TABLE IF NOT EXISTS `rental_inspections` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` INT(11) UNSIGNED NOT NULL,
  `inspection_type` ENUM('pickup', 'return') NOT NULL,
  `inspection_date` DATETIME NOT NULL,
  `inspector_id` INT(11) UNSIGNED,
  `exterior_condition` TEXT COMMENT 'JSON format with damages',
  `interior_condition` TEXT COMMENT 'JSON format',
  `tire_condition` VARCHAR(255),
  `fuel_level` DECIMAL(5,2),
  `mileage` INT(11),
  `cleanliness` ENUM('excellent', 'good', 'average', 'poor'),
  `damages` TEXT COMMENT 'List of damages',
  `photos` TEXT COMMENT 'JSON array of photo URLs',
  `signature` TEXT COMMENT 'Base64 signature',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`contract_id`) REFERENCES `rental_contracts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`inspector_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rental payments (paiements location)
CREATE TABLE IF NOT EXISTS `rental_payments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contract_id` INT(11) UNSIGNED NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash', 'credit_card', 'bank_transfer', 'check') DEFAULT 'cash',
  `reference_number` VARCHAR(50),
  `payment_type` ENUM('deposit', 'rental', 'additional', 'refund') DEFAULT 'rental',
  `notes` TEXT,
  `created_by` INT(11) UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`contract_id`) REFERENCES `rental_contracts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- ADVANCED PURCHASE WORKFLOW
-- ============================================================================

-- Purchase requests (demandes d'achat)
CREATE TABLE IF NOT EXISTS `purchase_requests` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_number` VARCHAR(50) UNIQUE NOT NULL,
  `requested_by` INT(11) UNSIGNED NOT NULL,
  `department` VARCHAR(50),
  `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  `request_date` DATE NOT NULL,
  `needed_by` DATE,
  `status` ENUM('draft', 'pending', 'approved', 'rejected', 'ordered', 'completed') DEFAULT 'draft',
  `approved_by` INT(11) UNSIGNED,
  `approved_date` DATE,
  `rejection_reason` TEXT,
  `total_amount` DECIMAL(10,2) DEFAULT 0,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Purchase request items (lignes demandes d'achat)
CREATE TABLE IF NOT EXISTS `purchase_request_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT(11) UNSIGNED NOT NULL,
  `part_id` INT(11) UNSIGNED,
  `description` VARCHAR(255) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `unit_price` DECIMAL(10,2),
  `total_price` DECIMAL(10,2),
  `notes` TEXT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`request_id`) REFERENCES `purchase_requests`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`part_id`) REFERENCES `parts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Delivery notes (bons de livraison)
CREATE TABLE IF NOT EXISTS `delivery_notes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_number` VARCHAR(50) UNIQUE NOT NULL,
  `purchase_order_id` INT(11) UNSIGNED NOT NULL,
  `supplier_id` INT(11) UNSIGNED NOT NULL,
  `delivery_date` DATE NOT NULL,
  `received_by` INT(11) UNSIGNED,
  `status` ENUM('pending', 'partial', 'complete', 'disputed') DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Delivery note items (lignes bons de livraison)
CREATE TABLE IF NOT EXISTS `delivery_note_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_note_id` INT(11) UNSIGNED NOT NULL,
  `part_id` INT(11) UNSIGNED,
  `description` VARCHAR(255) NOT NULL,
  `quantity_ordered` INT(11) NOT NULL,
  `quantity_received` INT(11) NOT NULL,
  `unit_price` DECIMAL(10,2),
  `condition` ENUM('good', 'damaged', 'missing') DEFAULT 'good',
  `notes` TEXT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`delivery_note_id`) REFERENCES `delivery_notes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`part_id`) REFERENCES `parts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- ADVANCED STOCK MANAGEMENT
-- ============================================================================

-- Stock locations (emplacements stock)
CREATE TABLE IF NOT EXISTS `stock_locations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `type` ENUM('warehouse', 'shelf', 'bin', 'vehicle') DEFAULT 'warehouse',
  `parent_id` INT(11) UNSIGNED COMMENT 'For hierarchical locations',
  `address` VARCHAR(255),
  `capacity` INT(11),
  `is_active` BOOLEAN DEFAULT TRUE,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`parent_id`) REFERENCES `stock_locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stock documents (bons de stock)
CREATE TABLE IF NOT EXISTS `stock_documents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_number` VARCHAR(50) UNIQUE NOT NULL,
  `document_type` ENUM('entry', 'exit', 'transfer', 'return', 'adjustment') NOT NULL,
  `document_date` DATE NOT NULL,
  `source_location_id` INT(11) UNSIGNED,
  `destination_location_id` INT(11) UNSIGNED,
  `reference_type` VARCHAR(50) COMMENT 'work_order, purchase_order, etc.',
  `reference_id` INT(11) UNSIGNED,
  `requested_by` INT(11) UNSIGNED,
  `approved_by` INT(11) UNSIGNED,
  `status` ENUM('draft', 'pending', 'approved', 'completed', 'cancelled') DEFAULT 'draft',
  `total_value` DECIMAL(10,2) DEFAULT 0,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`source_location_id`) REFERENCES `stock_locations`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`destination_location_id`) REFERENCES `stock_locations`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_document_type` (`document_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Stock document items (lignes bons de stock)
CREATE TABLE IF NOT EXISTS `stock_document_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_id` INT(11) UNSIGNED NOT NULL,
  `part_id` INT(11) UNSIGNED NOT NULL,
  `quantity` INT(11) NOT NULL,
  `unit_price` DECIMAL(10,2),
  `total_price` DECIMAL(10,2),
  `lot_number` VARCHAR(50),
  `expiry_date` DATE,
  `notes` TEXT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`document_id`) REFERENCES `stock_documents`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`part_id`) REFERENCES `parts`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Physical inventories (inventaires physiques)
CREATE TABLE IF NOT EXISTS `physical_inventories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inventory_number` VARCHAR(50) UNIQUE NOT NULL,
  `inventory_date` DATE NOT NULL,
  `location_id` INT(11) UNSIGNED,
  `counted_by` INT(11) UNSIGNED,
  `verified_by` INT(11) UNSIGNED,
  `status` ENUM('planned', 'in_progress', 'completed', 'validated') DEFAULT 'planned',
  `total_variance_value` DECIMAL(10,2) DEFAULT 0,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`location_id`) REFERENCES `stock_locations`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`counted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory items (lignes inventaire)
CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inventory_id` INT(11) UNSIGNED NOT NULL,
  `part_id` INT(11) UNSIGNED NOT NULL,
  `system_quantity` INT(11) NOT NULL,
  `physical_quantity` INT(11) NOT NULL,
  `variance` INT(11) GENERATED ALWAYS AS (`physical_quantity` - `system_quantity`) STORED,
  `unit_price` DECIMAL(10,2),
  `variance_value` DECIMAL(10,2),
  `notes` TEXT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`inventory_id`) REFERENCES `physical_inventories`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`part_id`) REFERENCES `parts`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- TCO (TOTAL COST OF OWNERSHIP) MODULE
-- ============================================================================

-- TCO configurations (paramètres TCO)
CREATE TABLE IF NOT EXISTS `tco_configurations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_type` VARCHAR(50) NOT NULL,
  `depreciation_method` ENUM('linear', 'declining', 'units_of_production') DEFAULT 'linear',
  `depreciation_years` INT(11) DEFAULT 5,
  `residual_value_percentage` DECIMAL(5,2) DEFAULT 20.00,
  `annual_mileage_estimate` INT(11) DEFAULT 20000,
  `insurance_annual_cost` DECIMAL(10,2),
  `tax_annual_cost` DECIMAL(10,2),
  `financing_interest_rate` DECIMAL(5,2),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TCO calculations (calculs TCO par véhicule)
CREATE TABLE IF NOT EXISTS `tco_calculations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `calculation_date` DATE NOT NULL,
  `calculation_period` VARCHAR(20) DEFAULT 'lifetime',
  `purchase_price` DECIMAL(10,2) NOT NULL,
  `current_value` DECIMAL(10,2),
  `depreciation_total` DECIMAL(10,2) DEFAULT 0,
  `fuel_cost_total` DECIMAL(10,2) DEFAULT 0,
  `maintenance_cost_total` DECIMAL(10,2) DEFAULT 0,
  `repairs_cost_total` DECIMAL(10,2) DEFAULT 0,
  `insurance_cost_total` DECIMAL(10,2) DEFAULT 0,
  `tax_cost_total` DECIMAL(10,2) DEFAULT 0,
  `financing_cost_total` DECIMAL(10,2) DEFAULT 0,
  `other_costs_total` DECIMAL(10,2) DEFAULT 0,
  `total_cost_ownership` DECIMAL(10,2) NOT NULL,
  `cost_per_km` DECIMAL(10,4),
  `cost_per_day` DECIMAL(10,2),
  `total_km` INT(11),
  `total_days` INT(11),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  KEY `idx_vehicle_date` (`vehicle_id`, `calculation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- MULTI-CASH MANAGEMENT
-- ============================================================================

-- Cash registers (caisses)
CREATE TABLE IF NOT EXISTS `cash_registers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE NOT NULL,
  `type` ENUM('main', 'secondary', 'petty_cash', 'mobile') DEFAULT 'secondary',
  `currency` VARCHAR(3) DEFAULT 'TND',
  `opening_balance` DECIMAL(10,2) DEFAULT 0,
  `current_balance` DECIMAL(10,2) DEFAULT 0,
  `location` VARCHAR(100),
  `responsible_user_id` INT(11) UNSIGNED,
  `status` ENUM('active', 'closed', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`responsible_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cash operations (opérations de caisse)
CREATE TABLE IF NOT EXISTS `cash_operations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `operation_number` VARCHAR(50) UNIQUE NOT NULL,
  `cash_register_id` INT(11) UNSIGNED NOT NULL,
  `operation_type` ENUM('deposit', 'withdrawal', 'transfer', 'opening', 'closing') NOT NULL,
  `operation_date` DATETIME NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash', 'check', 'card', 'transfer') DEFAULT 'cash',
  `reference_type` VARCHAR(50) COMMENT 'invoice, expense, etc.',
  `reference_id` INT(11) UNSIGNED,
  `destination_register_id` INT(11) UNSIGNED COMMENT 'For transfers',
  `description` TEXT,
  `performed_by` INT(11) UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`cash_register_id`) REFERENCES `cash_registers`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`destination_register_id`) REFERENCES `cash_registers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_operation_date` (`operation_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Check management (gestion chèques)
CREATE TABLE IF NOT EXISTS `checks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `check_number` VARCHAR(50) NOT NULL,
  `check_type` ENUM('received', 'issued') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `bank_name` VARCHAR(100),
  `account_number` VARCHAR(50),
  `payee` VARCHAR(100),
  `payer` VARCHAR(100),
  `status` ENUM('pending', 'deposited', 'cashed', 'bounced', 'cancelled') DEFAULT 'pending',
  `deposit_date` DATE,
  `cash_date` DATE,
  `cash_register_id` INT(11) UNSIGNED,
  `reference_type` VARCHAR(50),
  `reference_id` INT(11) UNSIGNED,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`cash_register_id`) REFERENCES `cash_registers`(`id`) ON DELETE SET NULL,
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bank reconciliation (rapprochement bancaire)
CREATE TABLE IF NOT EXISTS `bank_reconciliations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `reconciliation_date` DATE NOT NULL,
  `statement_date` DATE NOT NULL,
  `opening_balance` DECIMAL(10,2) NOT NULL,
  `closing_balance` DECIMAL(10,2) NOT NULL,
  `statement_balance` DECIMAL(10,2) NOT NULL,
  `difference` DECIMAL(10,2),
  `status` ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
  `reconciled_by` INT(11) UNSIGNED,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`reconciled_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default data for new modules
INSERT INTO `rental_rates` (`vehicle_type`, `rate_type`, `rate`, `deposit_amount`, `mileage_limit`, `excess_km_rate`) VALUES
('car', 'daily', 80.00, 500.00, 150, 0.50),
('car', 'weekly', 500.00, 500.00, 1000, 0.50),
('car', 'monthly', 1800.00, 500.00, 3000, 0.50),
('van', 'daily', 120.00, 800.00, 150, 0.70),
('truck', 'daily', 200.00, 1500.00, 200, 1.00);

INSERT INTO `stock_locations` (`name`, `code`, `type`) VALUES
('Entrepôt Principal', 'WH-MAIN', 'warehouse'),
('Atelier Mécanique', 'WH-WORKSHOP', 'warehouse'),
('Véhicules de Service', 'VH-SERVICE', 'vehicle');

INSERT INTO `cash_registers` (`name`, `code`, `type`, `opening_balance`, `current_balance`) VALUES
('Caisse Principale', 'CASH-MAIN', 'main', 5000.00, 5000.00),
('Caisse Secondaire', 'CASH-SEC', 'secondary', 1000.00, 1000.00),
('Petite Caisse', 'CASH-PETTY', 'petty_cash', 500.00, 500.00);
