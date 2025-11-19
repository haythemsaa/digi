-- DigiParc - Demo/Test Data
-- Seeds the database with sample companies and data for testing
-- Use this for development and demo environments only!

-- Create demo companies
INSERT INTO companies (
    company_name,
    company_code,
    legal_name,
    tax_id,
    email,
    phone,
    address,
    city,
    country,
    subscription_plan,
    subscription_status,
    trial_ends_at,
    max_users,
    max_vehicles,
    max_drivers,
    timezone,
    currency,
    language,
    primary_color,
    status,
    created_at,
    updated_at
) VALUES
-- Company 1: Starter plan
(
    'Transport Express SARL',
    'TEX2025001',
    'Transport Express Société à Responsabilité Limitée',
    '1234567/A/M/000',
    'contact@transport-express.tn',
    '+216 71 123 456',
    '123 Avenue Habib Bourguiba',
    'Tunis',
    'Tunisia',
    'starter',
    'trial',
    DATE_ADD(NOW(), INTERVAL 20 DAY),
    5,
    10,
    10,
    'Africa/Tunis',
    'TND',
    'fr',
    '#0d6efd',
    'active',
    NOW(),
    NOW()
),
-- Company 2: Professional plan
(
    'Logistics Pro SA',
    'LOG2025002',
    'Logistics Pro Société Anonyme',
    '9876543/B/M/000',
    'info@logistics-pro.tn',
    '+216 71 987 654',
    '456 Avenue de la République',
    'Sfax',
    'Tunisia',
    'professional',
    'active',
    NULL,
    20,
    50,
    50,
    'Africa/Tunis',
    'TND',
    'fr',
    '#198754',
    'active',
    NOW(),
    NOW()
),
-- Company 3: Enterprise plan
(
    'Mega Transport International',
    'MTI2025003',
    'Mega Transport International',
    '5555555/C/M/000',
    'contact@mega-transport.tn',
    '+216 70 555 555',
    '789 Route de Sousse',
    'Monastir',
    'Tunisia',
    'enterprise',
    'active',
    NULL,
    999,
    999,
    999,
    'Africa/Tunis',
    'EUR',
    'fr',
    '#dc3545',
    'active',
    NOW(),
    NOW()
);

-- Get company IDs for reference
SET @company1 = (SELECT id FROM companies WHERE company_code = 'TEX2025001');
SET @company2 = (SELECT id FROM companies WHERE company_code = 'LOG2025002');
SET @company3 = (SELECT id FROM companies WHERE company_code = 'MTI2025003');

-- Create users for Company 1 (Transport Express)
INSERT INTO users (
    company_id,
    first_name,
    last_name,
    email,
    password,
    role,
    status,
    created_at
) VALUES
(@company1, 'Ahmed', 'Ben Ali', 'ahmed@transport-express.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW()),
(@company1, 'Fatma', 'Trabelsi', 'fatma@transport-express.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NOW()),
(@company1, 'Mohamed', 'Hamdi', 'mohamed@transport-express.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', NOW());

-- Create users for Company 2 (Logistics Pro)
INSERT INTO users (
    company_id,
    first_name,
    last_name,
    email,
    password,
    role,
    status,
    created_at
) VALUES
(@company2, 'Sami', 'Jbeli', 'sami@logistics-pro.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW()),
(@company2, 'Leila', 'Mansour', 'leila@logistics-pro.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NOW());

-- Create users for Company 3 (Mega Transport)
INSERT INTO users (
    company_id,
    first_name,
    last_name,
    email,
    password,
    role,
    status,
    created_at
) VALUES
(@company3, 'Karim', 'Bouazizi', 'karim@mega-transport.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW()),
(@company3, 'Nadia', 'Gharbi', 'nadia@mega-transport.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active', NOW());

-- Create sample vehicles for Company 1
INSERT INTO vehicles (
    company_id,
    make,
    model,
    year,
    registration_number,
    vin,
    fuel_type,
    status,
    created_at
) VALUES
(@company1, 'Mercedes-Benz', 'Actros', 2022, '123 TUN 456', 'WDB96342162L123456', 'diesel', 'active', NOW()),
(@company1, 'Volvo', 'FH16', 2021, '789 TUN 012', 'YV2XCT1F3KB123789', 'diesel', 'active', NOW()),
(@company1, 'Renault', 'Master', 2023, '345 TUN 678', 'VF1MA000123456789', 'diesel', 'active', NOW());

-- Create sample drivers for Company 1
INSERT INTO drivers (
    company_id,
    first_name,
    last_name,
    license_number,
    license_expiry,
    phone,
    email,
    status,
    created_at
) VALUES
(@company1, 'Hedi', 'Slimani', 'DL123456789', DATE_ADD(NOW(), INTERVAL 2 YEAR), '+216 98 123 456', 'hedi@transport-express.tn', 'active', NOW()),
(@company1, 'Salah', 'Mansouri', 'DL987654321', DATE_ADD(NOW(), INTERVAL 3 YEAR), '+216 98 987 654', 'salah@transport-express.tn', 'active', NOW());

-- Create sample vehicles for Company 2
INSERT INTO vehicles (
    company_id,
    make,
    model,
    year,
    registration_number,
    vin,
    fuel_type,
    status,
    created_at
) VALUES
(@company2, 'Scania', 'R500', 2022, '111 TUN 222', 'YS2R4X50007K123456', 'diesel', 'active', NOW()),
(@company2, 'MAN', 'TGX', 2023, '333 TUN 444', 'WMAN23ZZ4EY123456', 'diesel', 'active', NOW()),
(@company2, 'DAF', 'XF', 2021, '555 TUN 666', 'XLRTE47MS0E123456', 'diesel', 'active', NOW()),
(@company2, 'Iveco', 'Stralis', 2022, '777 TUN 888', 'WJMM6212000123456', 'diesel', 'active', NOW());

-- Create sample vehicles for Company 3
INSERT INTO vehicles (
    company_id,
    make,
    model,
    year,
    registration_number,
    vin,
    fuel_type,
    status,
    created_at
) VALUES
(@company3, 'Mercedes-Benz', 'Atego', 2023, '999 TUN 000', 'WDB97042162L987654', 'diesel', 'active', NOW()),
(@company3, 'Renault', 'Premium', 2022, '222 TUN 333', 'VF624HRA000654321', 'diesel', 'active', NOW());

-- Create sample settings for each company
INSERT INTO settings (company_id, setting_key, setting_value, created_at) VALUES
-- Company 1 settings
(@company1, 'fuel_price_diesel', '2.5', NOW()),
(@company1, 'fuel_price_gasoline', '2.8', NOW()),
(@company1, 'maintenance_alert_km', '10000', NOW()),

-- Company 2 settings
(@company2, 'fuel_price_diesel', '2.4', NOW()),
(@company2, 'fuel_price_gasoline', '2.7', NOW()),
(@company2, 'maintenance_alert_km', '15000', NOW()),

-- Company 3 settings
(@company3, 'fuel_price_diesel', '2.3', NOW()),
(@company3, 'fuel_price_gasoline', '2.6', NOW()),
(@company3, 'maintenance_alert_km', '20000', NOW());

-- Summary
SELECT 'Demo data created successfully!' AS message;
SELECT '3 companies created:' AS summary;
SELECT company_name, company_code, subscription_plan, status FROM companies;
SELECT '' AS separator;
SELECT 'Users created:' AS summary;
SELECT u.email, u.role, c.company_name
FROM users u
JOIN companies c ON u.company_id = c.id
ORDER BY c.id, u.role;
SELECT '' AS separator;
SELECT 'Default password for all users: password' AS note;
