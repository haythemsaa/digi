# Pakiparc - Fleet Management System

**Version 3.0.0 (Enterprise Edition)** | Production Ready 🚀

A comprehensive multi-tenant SaaS platform for fleet management, vehicle tracking, maintenance scheduling, operational analytics, carbon tracking, GDPR compliance, and advanced automation.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)
![REST API](https://img.shields.io/badge/API-OpenAPI%203.0-green.svg)

---

## 🌟 Key Features

### 🏢 Multi-Tenant SaaS Platform
- ✅ **Unlimited Companies** - Manage multiple companies with complete data isolation
- ✅ **Super Admin Dashboard** - Cross-company visibility and management
- ✅ **Company Switching** - Seamless context switching between companies
- ✅ **Resource Quotas** - Per-company limits (users, vehicles, drivers)
- ✅ **Subscription Management** - Multiple plans, trials, and module access control
- ✅ **Custom Branding** - Company logos, colors, timezone, language, currency

### 🚗 Fleet Management
- Vehicle inventory and tracking
- Driver management with licensing
- Fuel consumption tracking
- Maintenance scheduling (preventive & corrective)
- GPS tracking and geofencing
- Real-time analytics and reporting

### 💰 Financial Management
- Cost tracking and analysis
- TCO (Total Cost of Ownership) calculator
- Invoicing and billing
- Expense management
- Multi-currency support (TND, EUR, USD)

### 📦 Procurement & Inventory
- Supplier management
- Purchase orders and requests
- Stock management
- Part inventory
- Movement tracking

### 🔐 Security & Access Control
- Role-based access control (super_admin, admin, manager, user)
- Module-based access (GPS, maintenance, fuel, HR, etc.)
- Data isolation between companies
- XSS and SQL injection protection
- Secure file uploads
- GDPR compliance with data portability and right to erasure

### 📊 Advanced Analytics & KPIs
- Real-time dashboard with 6 KPI categories
- Interactive Chart.js visualizations
- Trend analysis and performance comparisons
- Vehicle and driver performance rankings
- Custom date range reports
- PDF/Excel export functionality

### 🚨 Intelligent Alert System
- 6 alert types (maintenance, document expiry, fuel anomaly, stock, delivery delay, geofence)
- 4 priority levels (low, medium, high, critical)
- Automated rule-based detection
- CRON job for hourly monitoring
- Alert acknowledgment workflow
- Alert history and archiving

### 📧 Multi-Channel Notifications
- **Email** notifications via SMTP/PHPMailer
- **SMS** via Twilio API
- **Push** notifications (Web Push API)
- **WhatsApp** Business API integration
- User preference management
- Quiet hours support
- Priority threshold filtering
- Comprehensive notification logging

### 🌱 Carbon Tracking & CSR Reporting
- CO₂ emission calculation using ADEME factors
- Real-time emissions tracking per vehicle/driver/trip
- Eco-score (0-100) calculation
- Eco-driving recommendations with impact percentage
- CSR report generation with PDF export
- Carbon footprint trends and analytics
- Compliance with environmental regulations

### 🛡️ GDPR Compliance
- Data portability (JSON export)
- Right to erasure with anonymization
- Consent management system
- Audit logs for compliance
- Data processing registry
- Data breach reporting
- Privacy policy and cookie management

### 💾 Backup & Disaster Recovery
- Automated database backups (mysqldump + gzip)
- Automated file backups (tar.gz)
- Rotation policies (7 daily, 4 weekly, 12 monthly)
- Backup verification
- Interactive restore script
- RPO < 1 hour, RTO < 4 hours
- CRON job automation

### 🔌 Complete REST API
- RESTful architecture with JWT authentication
- Rate limiting (100 req/min)
- Complete OpenAPI 3.0 documentation
- Endpoints for all resources
- Bearer token authentication
- Error handling and validation
- JSON response format

### 🎨 Dark Mode & UX Improvements
- CSS custom properties for theming
- Automatic system theme detection
- localStorage persistence
- Smooth transitions
- Complete component coverage
- Chart.js dark mode integration
- Floating toggle button

### 💳 Payment Integration
- Stripe Checkout integration
- Webhook handling (5 event types)
- Subscription management
- Payment logging and history
- Automatic billing

### 💰 Dynamic Pricing Service
- Usage-based pricing (€10/user, €5/vehicle, €3/driver)
- Module-based pricing (€39-€149/module)
- Package pricing (€149-€599)
- Volume discounts up to 30%
- Overage calculation
- Invoice generation with VAT

### 🌍 Multi-Language Support (i18n)
- 5 languages: French, English, Spanish, German, Arabic
- RTL support for Arabic
- Locale-specific date/currency formats
- Translation functions: t(), __(), _e()
- Browser language detection
- User preference storage
- Language switcher component

---

## 📋 Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Multi-Tenant Architecture](#multi-tenant-architecture)
- [Documentation](#documentation)
- [API Reference](#api-reference)
- [Contributing](#contributing)

---

## 💻 Requirements

### System Requirements
- **PHP** >= 8.1
- **MySQL** >= 8.0 or MariaDB >= 10.5
- **Apache** >= 2.4 (with mod_rewrite)
- **Composer** (for dependencies)

### PHP Extensions
- `pdo_mysql`
- `gd` (for image processing)
- `mbstring`
- `curl`
- `openssl`
- `json`

### Server Configuration
- `upload_max_filesize` >= 2M
- `post_max_size` >= 3M
- `memory_limit` >= 128M

---

## 🚀 Installation

### Quick Install (5 minutes)

```bash
# 1. Clone repository
git clone https://github.com/your-org/pakiparc.git
cd pakiparc

# 2. Install dependencies
composer install

# 3. Create database
mysql -u root -p -e "CREATE DATABASE pakiparc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Import schema
mysql -u root -p pakiparc < database/schema.sql

# 5. Configure
cp config/config.example.php config/config.php
nano config/config.php  # Edit database credentials

# 6. Set permissions
chmod -R 755 .
chmod -R 775 public/uploads

# 7. Create super admin
mysql -u root -p pakiparc < database/seeds/super_admin.sql
```

### Default Credentials

**Super Admin:**
- Email: `admin@pakiparc.com`
- Password: `password` (change immediately!)

---

## ⚙️ Configuration

### Main Configuration (`config/config.php`)

```php
<?php
// Application
define('APP_NAME', 'Pakiparc');
define('APP_VERSION', '2.0.0');
define('APP_URL', 'http://localhost/pakiparc');

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'pakiparc');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');

// Security
define('SESSION_LIFETIME', 7200); // 2 hours
define('DEBUG', false); // Set to false in production
```

### Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName pakiparc.local
    DocumentRoot /var/www/html/pakiparc

    <Directory /var/www/html/pakiparc>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/pakiparc-error.log
    CustomLog ${APACHE_LOG_DIR}/pakiparc-access.log combined
</VirtualHost>
```

---

## 🚦 Quick Start

### 1. Login as Super Admin

Visit `http://localhost/pakiparc` and login with super admin credentials.

### 2. Create Your First Company

1. Navigate to **Entreprises** in the sidebar
2. Click **Créer une entreprise**
3. Fill in company details:
   - Company name, legal name, tax ID
   - Contact information
   - Subscription plan (starter, professional, enterprise)
   - Resource limits (users, vehicles, drivers)
   - Upload company logo (optional)
   - Set primary color, timezone, language
4. Click **Créer l'entreprise**

### 3. Switch to Company Context

1. Click the **company switcher** in the navbar
2. Select the company you just created
3. You're now in that company's context!

### 4. Add Users, Vehicles, Drivers

Now you can:
- Create users for this company
- Add vehicles to the fleet
- Register drivers
- Start tracking fuel, maintenance, etc.

---

## 🏗️ Multi-Tenant Architecture

### Data Isolation

Every query automatically filters by `company_id`:

```php
// Automatic company filtering
$vehicles = $vehicleModel->getAll(); // Only returns current company's vehicles

// Using the helper function
$sql = "SELECT * FROM vehicles WHERE " . companyFilter();
```

### Security Middleware

```php
// Require company context
CompanyMiddleware::requireCompanyContext();

// Require super admin
CompanyMiddleware::requireSuperAdmin();

// Check resource limits before creation
if (!CompanyMiddleware::checkResourceLimit('vehicles')) {
    flash('error', 'Limite de véhicules atteinte');
    redirect('subscription/upgrade');
}

// Require specific module access
CompanyMiddleware::requireModule('gps');
```

### Helper Functions

```php
// Get current company
$company = getCurrentCompany();
$companyId = getCurrentCompanyId();

// Check if user is super admin
if (isSuperAdmin()) {
    // Show super admin features
}

// Check resource limits
if (hasReachedVehicleLimit()) {
    // Show upgrade prompt
}

// Format with company settings
echo formatCompanyCurrency(1500.50);  // "1,500.50 TND"
echo formatCompanyDate('2024-01-15', true);  // "15/01/2024 10:30"
```

### Adding Multi-Tenant Support to New Features

1. **Add `company_id` column** to your table
2. **Use `companyFilter()`** in all SELECT queries
3. **Set `company_id`** from session on INSERT
4. **Validate company access** on UPDATE/DELETE

See [Developer Guide](docs/DEVELOPER_GUIDE.md) for detailed instructions.

---

## 📚 Documentation

Comprehensive documentation is available in the `docs/` directory:

- **[Multi-Tenant Improvements](docs/MULTI_TENANT_IMPROVEMENTS.md)** (500+ lines)
  - Complete architecture documentation
  - Component descriptions
  - Security patterns
  - Usage examples

- **[Deployment Checklist](docs/DEPLOYMENT_CHECKLIST.md)** (400+ lines)
  - Pre-deployment requirements
  - Step-by-step deployment guide
  - Security hardening procedures
  - Monitoring and backups

- **[Developer Guide](docs/DEVELOPER_GUIDE.md)** (600+ lines)
  - Adding multi-tenant support
  - Helper functions reference
  - Middleware usage patterns
  - Security best practices
  - Testing templates

- **[Completion Summary](docs/MULTI_TENANT_COMPLETION_SUMMARY.md)** (700+ lines)
  - Complete feature overview
  - Implementation statistics
  - Testing checklist
  - Troubleshooting guide

---

## 🏢 Subscription Plans

| Feature | Starter | Professional | Enterprise |
|---------|---------|--------------|------------|
| **Users** | 5 | 20 | Unlimited |
| **Vehicles** | 10 | 50 | Unlimited |
| **Drivers** | 10 | 50 | Unlimited |
| **GPS Tracking** | ❌ | ✅ | ✅ |
| **Maintenance Module** | ✅ | ✅ | ✅ |
| **Fuel Management** | ✅ | ✅ | ✅ |
| **HR Module** | ❌ | ✅ | ✅ |
| **Advanced Reports** | ❌ | ✅ | ✅ |
| **Custom Branding** | ❌ | ✅ | ✅ |
| **API Access** | ❌ | ❌ | ✅ |
| **Support** | Email | Priority | Dedicated |
| **Trial Period** | 30 days | 30 days | 30 days |

---

## 🗂️ Project Structure

```
pakiparc/
├── app/
│   ├── controllers/          # Application controllers
│   │   ├── Companies.php     # Company management (super admin)
│   │   ├── Dashboard.php     # Main dashboard
│   │   ├── Vehicles.php      # Fleet management
│   │   └── ...
│   ├── models/              # Database models
│   │   ├── Company.php      # Company model (20+ methods)
│   │   ├── Vehicle.php      # Multi-tenant vehicle model
│   │   └── ...
│   ├── views/               # View templates
│   │   ├── companies/       # Company management views
│   │   ├── dashboard/       # Dashboard views
│   │   └── includes/        # Shared components
│   ├── middleware/          # Security middleware
│   │   └── CompanyMiddleware.php  # Multi-tenant security
│   ├── helpers/             # Helper functions
│   │   ├── multi_tenant_helper.php  # 40+ multi-tenant utilities
│   │   └── init_helper.php          # Core helpers
│   └── core/                # Core framework files
├── config/                  # Configuration files
├── database/
│   ├── schema.sql          # Complete database schema
│   ├── migrations/         # Database migrations
│   └── seeds/              # Seed data
├── docs/                   # Documentation (1500+ lines)
├── public/
│   ├── assets/             # CSS, JS, images
│   └── uploads/            # Uploaded files
│       └── logos/          # Company logos
└── vendor/                 # Composer dependencies
```

---

## 🔒 Security

### Implemented Security Measures

- ✅ **XSS Protection** - Input sanitization on all user inputs
- ✅ **SQL Injection Prevention** - Prepared statements throughout
- ✅ **CSRF Protection** - Token validation on forms
- ✅ **File Upload Security** - Type and size validation
- ✅ **Session Security** - Secure session configuration
- ✅ **Password Hashing** - bcrypt with proper cost factor
- ✅ **Role-Based Access Control** - Granular permissions
- ✅ **Data Isolation** - Company-level data separation
- ✅ **Audit Logging** - Activity tracking for compliance

### Security Best Practices

```php
// Always sanitize input
$name = sanitize($_POST['name']);

// Use prepared statements
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $userId);

// Validate company access
if (!validateCompanyAccess($dataCompanyId)) {
    redirect('dashboard');
}

// Check authentication
requireCompany('login');
```

---

## 🧪 Testing

### Manual Testing Checklist

```bash
# Super Admin Tests
✓ Login as super admin
✓ Create new company
✓ Edit company details
✓ Upload company logo
✓ Set resource limits
✓ Manage subscription
✓ Switch to company context
✓ Switch back to super admin

# Data Isolation Tests
✓ Company A cannot see Company B data
✓ Super admin sees all data
✓ Resource limits are enforced
✓ Module access is controlled

# Security Tests
✓ XSS attempts are blocked
✓ SQL injection attempts fail
✓ Cross-company access prevented
✓ File upload security works
```

---

## 🐛 Troubleshooting

### Common Issues

**File uploads not working:**
```bash
chmod 775 public/uploads
chown www-data:www-data public/uploads
```

**Company data not isolated:**
```php
// Verify company_id in session
var_dump($_SESSION['company_id']);

// Ensure companyFilter() is used
$sql = "SELECT * FROM vehicles WHERE " . companyFilter();
```

**Resource limits not enforced:**
```php
// Check company limits
SELECT max_vehicles FROM companies WHERE id = 1;

// Verify middleware is called
CompanyMiddleware::checkResourceLimit('vehicles');
```

See [Deployment Checklist](docs/DEPLOYMENT_CHECKLIST.md) for more solutions.

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Add PHPDoc comments to all public methods
- Always use `companyFilter()` in queries
- Validate and sanitize all user input
- Write security-conscious code

---

## 📈 Roadmap

### Version 2.1 (Q1 2026)
- [ ] Email notifications for trial expiration
- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] Advanced analytics dashboard
- [ ] Export to Excel/PDF improvements

### Version 2.2 (Q2 2026)
- [ ] RESTful API for third-party integrations
- [ ] Mobile app (iOS/Android)
- [ ] Real-time notifications
- [ ] Advanced reporting with custom filters

### Version 3.0 (Q3 2026)
- [ ] White-label support
- [ ] Microservices architecture
- [ ] Machine learning for predictive maintenance
- [ ] IoT device integration

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👥 Support

- **Documentation:** [docs/](docs/)
- **Issues:** [GitHub Issues](https://github.com/your-org/pakiparc/issues)
- **Email:** support@pakiparc.com
- **Website:** https://pakiparc.com

---

## 🙏 Acknowledgments

- Bootstrap 5 for UI framework
- Font Awesome for icons
- Chart.js for data visualization
- Leaflet.js for GPS mapping
- All contributors and testers

---

## 📊 Statistics

- **Total Code:** 5,000+ lines
- **Documentation:** 1,500+ lines
- **Files:** 25+
- **Models:** 12 multi-tenant models
- **Controllers:** 15+ controllers
- **Views:** 30+ views
- **Helper Functions:** 40+
- **Middleware:** Complete security layer
- **Status:** ✅ Production Ready

---

**Made with ❤️ by the Pakiparc Team**

*Version 2.0.0 | Multi-Tenant SaaS Platform | November 2025*
