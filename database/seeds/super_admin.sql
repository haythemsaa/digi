-- Pakiparc - Super Admin Seed Data
-- Creates a default super admin account for initial setup
-- WARNING: Change the password immediately after first login!

-- Create super admin user
INSERT INTO users (
    first_name,
    last_name,
    email,
    password,
    role,
    status,
    is_super_admin,
    created_at,
    updated_at
) VALUES (
    'Super',
    'Admin',
    'admin@pakiparc.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'admin',
    'active',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    is_super_admin = 1,
    status = 'active';

-- Confirmation message
SELECT 'Super admin account created successfully!' AS message;
SELECT CONCAT('Email: admin@pakiparc.com') AS credentials;
SELECT CONCAT('Password: password (CHANGE IMMEDIATELY!)') AS warning;
