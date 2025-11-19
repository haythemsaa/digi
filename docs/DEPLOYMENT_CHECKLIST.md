# DigiParc Multi-Tenant Deployment Checklist

## Pre-Deployment

### 1. Database Setup
- [ ] Run all database migrations
- [ ] Create `companies` table with all fields
- [ ] Ensure all existing tables have `company_id` column
- [ ] Add foreign key constraints for `company_id`
- [ ] Create indexes on `company_id` columns for performance
- [ ] Seed at least one super admin user account
- [ ] Verify database collation supports multi-language (utf8mb4_unicode_ci recommended)

### 2. File System
- [ ] Verify `/public/uploads` directory exists with write permissions (755)
- [ ] Verify `/public/uploads/logos` subdirectory exists
- [ ] Check `.gitignore` is properly configured for uploads
- [ ] Test file upload functionality
- [ ] Configure max upload size in PHP (php.ini):
  ```ini
  upload_max_filesize = 2M
  post_max_size = 3M
  ```

### 3. Configuration
- [ ] Set correct `APP_URL` in `config/config.php`
- [ ] Configure database credentials
- [ ] Set secure session configuration
- [ ] Enable HTTPS in production
- [ ] Configure proper error reporting (disable in production)
- [ ] Set timezone in php.ini to match primary region

### 4. Security
- [ ] Ensure `.htaccess` file is in place
- [ ] Verify mod_rewrite is enabled
- [ ] Test that directory browsing is disabled
- [ ] Verify security headers are set (X-Frame-Options, X-XSS-Protection, etc.)
- [ ] Use prepared statements for all database queries (already implemented)
- [ ] Sanitize all user inputs (already implemented)
- [ ] Test CSRF protection
- [ ] Implement rate limiting for login attempts
- [ ] Use strong session management

### 5. Multi-Tenant Features
- [ ] Test company creation workflow
- [ ] Verify data isolation between companies
- [ ] Test super admin company switching
- [ ] Verify resource limit enforcement
- [ ] Test subscription status checks
- [ ] Verify trial expiration handling
- [ ] Test company branding (logo, colors)

## Deployment Steps

### 1. Server Preparation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y apache2 php8.1 php8.1-mysql php8.1-gd php8.1-curl php8.1-mbstring

# Enable required Apache modules
sudo a2enmod rewrite
sudo a2enmod headers

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/digiparc
sudo chmod -R 755 /var/www/html/digiparc
sudo chmod -R 775 /var/www/html/digiparc/public/uploads
```

### 2. Application Setup
```bash
# Navigate to application directory
cd /var/www/html/digiparc

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set environment to production
# Edit config/config.php and set DEBUG to false

# Clear any development caches
# (if applicable)
```

### 3. Database Migration
```bash
# Import database schema
mysql -u root -p digiparc < database/schema.sql

# Run migrations
php cli/migrate.php
```

### 4. Create Super Admin
```sql
-- Run this SQL to create your first super admin
INSERT INTO users (
    first_name,
    last_name,
    email,
    password,
    role,
    status,
    is_super_admin,
    created_at
) VALUES (
    'Super',
    'Admin',
    'admin@digiparc.com',
    '$2y$10$...',  -- Use password_hash('your_password', PASSWORD_DEFAULT)
    'admin',
    'active',
    1,
    NOW()
);
```

### 5. Create First Company
1. Login as super admin
2. Navigate to `/companies/create`
3. Fill in company details
4. Upload company logo
5. Set resource limits
6. Configure trial period
7. Create company

## Post-Deployment

### 1. Testing
- [ ] Test super admin login
- [ ] Create a test company
- [ ] Switch to test company context
- [ ] Create test users, vehicles, drivers
- [ ] Verify data isolation
- [ ] Test all CRUD operations
- [ ] Verify module access control
- [ ] Test resource limit enforcement
- [ ] Test file uploads (company logos)
- [ ] Verify email notifications work
- [ ] Test subscription expiration
- [ ] Test multi-language support

### 2. Performance
- [ ] Enable OPcache
- [ ] Configure PHP memory limits appropriately
- [ ] Set up database query caching
- [ ] Implement Redis/Memcached for session storage (optional)
- [ ] Configure CDN for static assets (optional)
- [ ] Enable Gzip compression

### 3. Monitoring
- [ ] Set up error logging
- [ ] Configure application logs
- [ ] Set up database backup schedule
- [ ] Implement health check endpoint
- [ ] Configure uptime monitoring
- [ ] Set up alerts for trial expirations
- [ ] Monitor disk space for uploads

### 4. Backups
- [ ] Configure automated database backups (daily)
- [ ] Back up uploaded files (daily)
- [ ] Test backup restoration procedure
- [ ] Store backups off-site
- [ ] Document backup/restore process

### 5. Documentation
- [ ] Document super admin procedures
- [ ] Create user guides for company admins
- [ ] Document API endpoints (if applicable)
- [ ] Create troubleshooting guide
- [ ] Document deployment procedure

## Security Hardening

### 1. Server Level
```bash
# Disable directory listing
sudo nano /etc/apache2/sites-available/digiparc.conf
# Add: Options -Indexes

# Hide Apache version
sudo nano /etc/apache2/conf-enabled/security.conf
# Set: ServerTokens Prod
# Set: ServerSignature Off

# Configure firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 2. Application Level
- [ ] Use HTTPS only (redirect HTTP to HTTPS)
- [ ] Implement Content Security Policy headers
- [ ] Enable HSTS (HTTP Strict Transport Security)
- [ ] Sanitize all file uploads
- [ ] Validate file types and sizes
- [ ] Use parameterized queries (already implemented)
- [ ] Implement session timeout
- [ ] Add 2FA for super admin accounts (recommended)

### 3. Database Security
- [ ] Use separate database user (not root)
- [ ] Grant minimum required permissions
- [ ] Disable remote database access
- [ ] Use strong database passwords
- [ ] Encrypt sensitive data at rest

## Common Issues & Solutions

### Issue: File uploads not working
**Solution:**
```bash
# Check permissions
sudo chmod 775 /var/www/html/digiparc/public/uploads
sudo chown -R www-data:www-data /var/www/html/digiparc/public/uploads

# Check PHP upload settings
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### Issue: Company data not isolated
**Solution:**
- Verify company_id is set in session
- Check all queries include company_id filter
- Review CompanyMiddleware::requireCompanyContext()
- Ensure models use companyFilter() helper

### Issue: Resource limits not enforced
**Solution:**
- Verify company limits are set in database
- Check CompanyMiddleware::checkResourceLimit()
- Ensure controllers call middleware before create operations

### Issue: Module access not working
**Solution:**
- Verify subscription plan in database
- Check module mappings in hasModuleAccess()
- Ensure plan includes required modules

## Maintenance

### Daily
- [ ] Check error logs
- [ ] Monitor disk space
- [ ] Review failed login attempts

### Weekly
- [ ] Review company trial expirations
- [ ] Check resource usage across companies
- [ ] Review system performance metrics

### Monthly
- [ ] Update dependencies
- [ ] Review and rotate logs
- [ ] Test backup restoration
- [ ] Security updates check
- [ ] Performance optimization review

## Support Contacts

- **Technical Support:** support@digiparc.com
- **Emergency Contact:** +216 XX XXX XXX
- **Documentation:** https://docs.digiparc.com

## Version

- **Current Version:** 2.0.0 (Multi-Tenant)
- **Last Updated:** 2025-11-19
- **Deployment Date:** ___________
- **Deployed By:** ___________
