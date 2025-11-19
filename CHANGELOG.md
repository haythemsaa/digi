# Changelog

All notable changes to DigiParc Fleet Management System will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-11-19

### 🎉 Major Release - Multi-Tenant SaaS Platform

This is a complete transformation of DigiParc into a multi-tenant SaaS platform with comprehensive company management, subscription handling, and data isolation.

### Added

#### Multi-Tenant Architecture
- **Company Management System**
  - Complete CRUD operations for companies
  - Company model with 20+ methods
  - Companies controller with full admin interface
  - Company creation, editing, viewing, and deletion
  - Soft delete with data retention
  - Unique company code generation

#### Super Admin Features
- **Super Admin Dashboard**
  - Cross-company visibility
  - Global statistics and analytics
  - Company impersonation/switching
  - Subscription management interface
  - Resource limit configuration

#### Data Isolation
- **Automatic Company Filtering**
  - `companyFilter()` helper function
  - Session-based company context
  - Cross-company access prevention
  - Validated data access throughout

#### Resource Management
- **Per-Company Quotas**
  - Configurable user limits
  - Vehicle count restrictions
  - Driver limit enforcement
  - Real-time usage tracking
  - Visual progress indicators (70% yellow, 90% red)

#### Subscription System
- **Multiple Plans**
  - Starter plan (5 users, 10 vehicles, 10 drivers)
  - Professional plan (20 users, 50 vehicles, 50 drivers)
  - Enterprise plan (unlimited resources)
  - Custom plan support

- **Trial Management**
  - Configurable trial periods
  - Expiration tracking
  - 7-day warning alerts
  - Trial extension functionality

- **Module Access Control**
  - GPS tracking module
  - Maintenance module
  - Fuel management
  - HR module
  - Advanced reports
  - API access (enterprise only)

#### Branding & Customization
- **Company-Specific Settings**
  - Custom logo upload (2MB max, JPG/PNG/GIF/WebP)
  - Primary and secondary colors
  - Timezone configuration
  - Language selection (French, Arabic, English)
  - Currency settings (TND, EUR, USD)
  - Date/time format preferences

#### Security Enhancements
- **CompanyMiddleware**
  - `requireCompanyContext()` - Enforce valid company session
  - `requireSuperAdmin()` - Super admin only access
  - `checkSubscription()` - Verify active subscription
  - `requireModule()` - Module access control
  - `checkResourceLimit()` - Enforce quotas
  - `validateDataAccess()` - Cross-company protection
  - `getCompanyLimits()` - Real-time limit tracking

- **File Upload Security**
  - Type validation (images only)
  - Size limits (2MB max)
  - Secure filename generation
  - Malicious file prevention

#### Helper Functions
- **40+ Multi-Tenant Helpers** (`app/helpers/multi_tenant_helper.php`)
  - Company context management
  - Data filtering utilities
  - Resource limit checking
  - Subscription status checks
  - Settings management
  - Session handling
  - Branding utilities

- **Enhanced Core Helpers** (`app/helpers/init_helper.php`)
  - Flash messages with Bootstrap 5 icons
  - Redirects and authentication
  - Currency formatting
  - Date formatting
  - XSS protection with `sanitize()`
  - Debug helper `dd()`

#### User Interface
- **Company Management Views**
  - `companies/index.php` - List all companies with statistics
  - `companies/create.php` - Create new company form
  - `companies/view.php` - Detailed company view with stats
  - `companies/edit.php` - Edit company with all fields

- **Enhanced Navigation**
  - Company switcher component in navbar
  - Current company indicator
  - Quick switch between companies
  - Super admin menu in sidebar
  - Trial and subscription badges

- **Enhanced Dashboard**
  - Super admin context banner
  - Trial expiration warnings
  - Resource limit warnings (80%+ usage)
  - Resource usage cards with progress bars
  - Company-specific subscription display

#### Database Changes
- **New Tables**
  - `companies` - Complete multi-tenant company management

- **Table Updates**
  - Added `company_id` to all existing tables:
    - `users`
    - `vehicles`
    - `drivers`
    - `fuel_transactions`
    - `missions`
    - `maintenance`
    - `tracking_data`
    - `financial_records`
    - `inventory_items`
    - `suppliers`
  - Foreign key constraints
  - Performance indexes on `company_id`

- **Settings Enhancement**
  - Two-level architecture (global + company)
  - Automatic fallback mechanism
  - Type-aware value parsing

#### Models Enhanced
- **Company Model** (`app/models/Company.php`)
  - getAllCompanies()
  - getCompanyById()
  - getCompanyByCode()
  - createCompany()
  - updateCompany()
  - updateStatus()
  - updateSubscriptionStatus()
  - deleteCompany() - soft delete
  - getCompanyStats()
  - getCompaniesWithStats()
  - getExpiringTrials()
  - companyCodeExists()
  - extendTrial()
  - getGlobalStats()
  - updateLogo()
  - updateBranding()

- **All Existing Models** updated with:
  - Multi-tenant support
  - Company filtering
  - Data isolation
  - Security validation

#### Documentation
- **MULTI_TENANT_IMPROVEMENTS.md** (500+ lines)
  - Complete architecture documentation
  - Component descriptions
  - Usage examples
  - Security patterns
  - Deployment checklist

- **DEPLOYMENT_CHECKLIST.md** (400+ lines)
  - Pre-deployment requirements
  - Step-by-step deployment guide
  - Post-deployment testing
  - Security hardening
  - Performance optimization
  - Monitoring and backups
  - Common issues and solutions

- **DEVELOPER_GUIDE.md** (600+ lines)
  - Adding multi-tenant support to new features
  - Helper functions reference
  - Middleware usage patterns
  - Common coding patterns
  - Security best practices
  - Testing templates
  - Performance tips
  - Troubleshooting guide

- **MULTI_TENANT_COMPLETION_SUMMARY.md** (700+ lines)
  - Complete feature overview
  - Implementation statistics
  - Testing checklist
  - Git commit history
  - Quick start guide

- **README.md** - Completely updated for v2.0.0

#### Database Seeds
- **super_admin.sql** - Creates default super admin account
- **demo_data.sql** - Sample companies and data for testing

### Changed

#### Core Architecture
- Session management enhanced for multi-tenant context
- Authentication system updated to load company data
- All controllers updated to use CompanyMiddleware
- All models updated with company filtering
- All views updated with company awareness

#### Settings System
- Migrated to two-level (global + company) architecture
- Enhanced Setting model with fallback logic
- Type parsing for boolean, numeric, and JSON values

#### Dashboard
- Multi-tenant context awareness
- Resource usage displays
- Subscription status indicators
- Trial warnings

### Security
- Enhanced XSS protection throughout
- SQL injection prevention with prepared statements
- CSRF protection on all forms
- Secure file upload handling
- Session security improvements
- Company data isolation enforcement

### Performance
- Database indexes on all company_id columns
- Optimized queries with proper filtering
- Efficient session management
- Cached company settings

---

## [1.0.0] - 2024-11-18

### Initial Release

#### Core Features
- Fleet management (vehicles, drivers)
- GPS tracking and geofencing
- Fuel management
- Maintenance scheduling
- Transport orders (TMS)
- Financial management
- Procurement
- Inventory management
- User management with roles
- Reports and analytics

#### Technology Stack
- PHP 7.4+
- MySQL/MariaDB
- Bootstrap 5
- Chart.js
- Leaflet.js for maps
- MVC architecture

---

## Version Comparison

### v2.0.0 vs v1.0.0

| Feature | v1.0.0 | v2.0.0 |
|---------|--------|--------|
| Architecture | Single-tenant | Multi-tenant SaaS |
| Companies | 1 (implied) | Unlimited |
| Data Isolation | N/A | Complete |
| Subscription Plans | No | Yes (3 plans + trial) |
| Resource Limits | No | Yes (per company) |
| Custom Branding | No | Yes (logo, colors) |
| Super Admin | No | Yes (full management) |
| Module Access Control | No | Yes (plan-based) |
| Company Switching | No | Yes |
| Multi-currency | No | Yes (TND, EUR, USD) |
| Multi-language | Partial | Full (fr, ar, en) |
| Documentation | Basic | Comprehensive (1500+ lines) |

---

## Upgrade Guide

### From v1.0.0 to v2.0.0

**⚠️ IMPORTANT:** This is a major version upgrade with breaking changes. Full database migration required.

#### Before Upgrading

1. **Backup everything**
   ```bash
   mysqldump -u root -p digiparc > backup_v1.sql
   tar -czf backup_files_v1.tar.gz public/uploads
   ```

2. **Review documentation**
   - Read MULTI_TENANT_IMPROVEMENTS.md
   - Review DEPLOYMENT_CHECKLIST.md
   - Check DEVELOPER_GUIDE.md for code changes

#### Upgrade Steps

1. **Update code**
   ```bash
   git pull origin main
   composer install
   ```

2. **Run migrations**
   ```bash
   mysql -u root -p digiparc < database/migration_multi_tenant.sql
   ```

3. **Create companies table**
   ```bash
   mysql -u root -p digiparc < database/schema.sql
   ```

4. **Create super admin**
   ```bash
   mysql -u root -p digiparc < database/seeds/super_admin.sql
   ```

5. **Create first company**
   - Login as super admin
   - Navigate to Companies
   - Create your company
   - Assign existing data to this company

6. **Test thoroughly**
   - Verify data isolation
   - Test resource limits
   - Check module access
   - Validate security

#### Post-Upgrade

1. Update all users to notify them of changes
2. Train admins on multi-tenant features
3. Configure subscription plans
4. Set up monitoring for trials and limits
5. Review security settings

---

## Future Roadmap

### v2.1.0 (Q1 2026)
- Email notifications for trials
- Payment gateway integration
- Advanced analytics
- Mobile app improvements

### v2.2.0 (Q2 2026)
- RESTful API
- Real-time notifications
- Custom reporting
- White-label support

### v3.0.0 (Q3 2026)
- Microservices architecture
- AI/ML features
- IoT integration
- Blockchain audit trails

---

## Support & Feedback

For questions, issues, or suggestions:
- **Issues:** [GitHub Issues](https://github.com/your-org/digiparc/issues)
- **Email:** support@digiparc.com
- **Documentation:** [docs/](docs/)

---

*This changelog follows [Keep a Changelog](https://keepachangelog.com/) format.*
