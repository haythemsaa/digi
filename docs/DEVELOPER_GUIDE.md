# Pakiparc Multi-Tenant Developer Guide

## Quick Reference

### Adding Multi-Tenant Support to a New Feature

When creating a new feature or module, follow these steps to ensure proper multi-tenant isolation:

#### 1. Database Table

Always include `company_id` in your table schema:

```sql
CREATE TABLE new_feature (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    -- your fields here
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id)
);
```

#### 2. Model

```php
class NewFeature {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // ALWAYS include company filter
    public function getAll() {
        $this->db->query("
            SELECT * FROM new_feature
            WHERE " . companyFilter() . "
            ORDER BY created_at DESC
        ");
        return $this->db->fetchAll();
    }

    // ALWAYS set company_id on create
    public function create($data) {
        $this->db->query("
            INSERT INTO new_feature (company_id, field1, field2)
            VALUES (:company_id, :field1, :field2)
        ");

        $this->db->bind(':company_id', getCurrentCompanyId());
        $this->db->bind(':field1', $data['field1']);
        $this->db->bind(':field2', $data['field2']);

        return $this->db->execute();
    }

    // ALWAYS validate company access
    public function getById($id) {
        $this->db->query("
            SELECT * FROM new_feature
            WHERE id = :id AND " . companyFilter()
        ");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }
}
```

#### 3. Controller

```php
class NewFeatures extends Controller {

    public function __construct() {
        // Require company context for all methods
        CompanyMiddleware::requireCompanyContext();

        $this->newFeatureModel = $this->model('NewFeature');
    }

    public function create() {
        // Check resource limits if applicable
        if (!CompanyMiddleware::checkResourceLimit('new_features')) {
            flash('error', 'Limite atteinte pour votre abonnement');
            $this->redirect('newfeatures');
        }

        // Check module access if this is a premium feature
        CompanyMiddleware::requireModule('premium_feature');

        // Your creation logic here
    }

    public function edit($id) {
        $item = $this->newFeatureModel->getById($id);

        if (!$item) {
            flash('error', 'Ressource introuvable');
            $this->redirect('newfeatures');
        }

        // Validate company access (redundant but safe)
        if (!validateCompanyAccess($item['company_id'])) {
            $this->redirect('dashboard');
        }

        // Your edit logic here
    }
}
```

## Helper Functions Reference

### Company Context

```php
// Get current company ID
$companyId = getCurrentCompanyId();

// Get full company data
$company = getCurrentCompany();

// Check if super admin
if (isSuperAdmin()) {
    // Special logic for super admins
}

// Require company context (redirect if not set)
requireCompany();
```

### Data Filtering

```php
// SQL WHERE clause for company filtering
$sql = "SELECT * FROM table WHERE " . companyFilter('t');
// Super admin sees all, regular users see only their company

// Check access to specific company's data
if (canAccessCompany($dataCompanyId)) {
    // Allow access
}

// Validate and redirect if unauthorized
if (!validateCompanyAccess($dataCompanyId)) {
    // Automatically flashes error and returns false
}
```

### Resource Limits

```php
// Check vehicle limit
if (hasReachedVehicleLimit()) {
    flash('error', 'Limite de véhicules atteinte');
}

// Check driver limit
if (hasReachedDriverLimit()) {
    flash('error', 'Limite de chauffeurs atteinte');
}

// Check user limit
if (hasReachedUserLimit()) {
    flash('error', 'Limite d\'utilisateurs atteinte');
}

// In middleware (recommended)
CompanyMiddleware::checkResourceLimit('vehicles');
```

### Subscription & Modules

```php
// Check subscription status
$status = getCompanySubscriptionStatus();

// Check if subscription is active
if (isCompanySubscriptionActive()) {
    // Allow access
}

// Check trial status
if (isCompanyInTrial()) {
    $days = getTrialDaysRemaining();
    echo "Trial expires in {$days} days";
}

// Check module access
if (hasModuleAccess('gps')) {
    // Show GPS features
}

// In middleware (recommended)
CompanyMiddleware::requireModule('gps');
```

### Company Settings

```php
// Get specific setting
$timezone = getCompanySetting('timezone', 'Africa/Tunis');

// Format currency
echo formatCompanyCurrency(1500.50); // "1,500.50 TND"

// Format date
echo formatCompanyDate('2024-01-15', true); // "15/01/2024 10:30"

// Get company details
$name = getCompanyName();
$logo = getCompanyLogo();
$color = getCompanyPrimaryColor();
```

### Session Management

```php
// Load company into session (for super admin switching)
loadCompanyIntoSession($companyId);

// Clear company from session
clearCompanyFromSession();

// Log activity
logCompanyActivity('vehicle_created', 'Created vehicle ABC-123', [
    'vehicle_id' => $vehicleId
]);
```

## Middleware Usage

### Company Context Middleware

```php
// Require company context for entire controller
public function __construct() {
    CompanyMiddleware::requireCompanyContext();
}

// Require super admin
CompanyMiddleware::requireSuperAdmin();

// Check subscription status
CompanyMiddleware::checkSubscription();

// Require specific module
CompanyMiddleware::requireModule('gps');

// Check resource limit before creation
if (!CompanyMiddleware::checkResourceLimit('vehicles')) {
    // Limit reached, show error
}

// Validate data access
CompanyMiddleware::validateDataAccess($dataCompanyId);

// Get current limits
$limits = CompanyMiddleware::getCompanyLimits();
/*
Returns:
[
    'vehicles' => ['current' => 8, 'max' => 10, 'reached' => false],
    'drivers' => ['current' => 5, 'max' => 10, 'reached' => false],
    'users' => ['current' => 3, 'max' => 5, 'reached' => false]
]
*/
```

## Common Patterns

### Pattern 1: List with Pagination

```php
public function index($page = 1) {
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $this->db->query("
        SELECT * FROM items
        WHERE " . companyFilter() . "
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");

    $this->db->bind(':limit', $limit);
    $this->db->bind(':offset', $offset);

    $items = $this->db->fetchAll();

    // Count total for pagination
    $this->db->query("
        SELECT COUNT(*) as total FROM items
        WHERE " . companyFilter()
    ");
    $total = $this->db->fetch()['total'];

    return [
        'items' => $items,
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ];
}
```

### Pattern 2: Search with Company Filter

```php
public function search($keyword) {
    $this->db->query("
        SELECT * FROM items
        WHERE " . companyFilter() . "
        AND (name LIKE :keyword OR description LIKE :keyword)
        ORDER BY created_at DESC
    ");

    $this->db->bind(':keyword', "%{$keyword}%");

    return $this->db->fetchAll();
}
```

### Pattern 3: Stats/Dashboard Data

```php
public function getDashboardStats() {
    $companyId = getCurrentCompanyId();

    // If super admin, get global stats
    if (isSuperAdmin()) {
        return $this->getGlobalStats();
    }

    // Get company-specific stats
    $this->db->query("
        SELECT
            (SELECT COUNT(*) FROM vehicles WHERE company_id = :company_id) as total_vehicles,
            (SELECT COUNT(*) FROM drivers WHERE company_id = :company_id) as total_drivers,
            (SELECT COUNT(*) FROM users WHERE company_id = :company_id) as total_users
    ");

    $this->db->bind(':company_id', $companyId);

    return $this->db->fetch();
}
```

### Pattern 4: Join with Company Filter

```php
public function getVehiclesWithDrivers() {
    $this->db->query("
        SELECT v.*, d.first_name, d.last_name
        FROM vehicles v
        LEFT JOIN drivers d ON v.driver_id = d.id
        WHERE " . companyFilter('v') . "
        AND " . companyFilter('d') . "
        ORDER BY v.created_at DESC
    ");

    return $this->db->fetchAll();
}
```

## Security Best Practices

### 1. Always Filter by Company

```php
// ❌ WRONG - No company filter
$this->db->query("SELECT * FROM vehicles WHERE id = :id");

// ✅ CORRECT - With company filter
$this->db->query("SELECT * FROM vehicles WHERE id = :id AND " . companyFilter());
```

### 2. Validate Company Access on Updates/Deletes

```php
// ❌ WRONG - Direct update without validation
public function delete($id) {
    $this->db->query("DELETE FROM items WHERE id = :id");
    $this->db->bind(':id', $id);
    return $this->db->execute();
}

// ✅ CORRECT - Validate ownership first
public function delete($id) {
    // Get item and verify company ownership
    $item = $this->getById($id); // This method includes company filter

    if (!$item) {
        return false; // Not found or wrong company
    }

    // Now safe to delete
    $this->db->query("DELETE FROM items WHERE id = :id AND " . companyFilter());
    $this->db->bind(':id', $id);
    return $this->db->execute();
}
```

### 3. Set Company ID on Create

```php
// ❌ WRONG - Trusting client input
public function create($data) {
    $companyId = $data['company_id']; // User could manipulate this!
}

// ✅ CORRECT - Use session company ID
public function create($data) {
    $companyId = getCurrentCompanyId(); // Safe from manipulation
}
```

### 4. Handle Super Admin Separately

```php
public function getAll() {
    if (isSuperAdmin()) {
        // Super admin sees everything
        $this->db->query("SELECT * FROM items ORDER BY created_at DESC");
    } else {
        // Regular users see only their company
        $this->db->query("SELECT * FROM items WHERE " . companyFilter());
    }

    return $this->db->fetchAll();
}
```

## Testing Multi-Tenant Features

### Unit Test Template

```php
// Test data isolation
public function testDataIsolation() {
    // Create item for company 1
    $_SESSION['company_id'] = 1;
    $item1Id = $this->model->create(['name' => 'Item 1']);

    // Create item for company 2
    $_SESSION['company_id'] = 2;
    $item2Id = $this->model->create(['name' => 'Item 2']);

    // Company 1 should only see item 1
    $_SESSION['company_id'] = 1;
    $items = $this->model->getAll();

    $this->assertCount(1, $items);
    $this->assertEquals($item1Id, $items[0]['id']);
}

// Test resource limits
public function testResourceLimit() {
    $_SESSION['company_data'] = ['max_vehicles' => 2];

    // Create 2 vehicles (should succeed)
    $this->model->create(['plate' => 'ABC-1']);
    $this->model->create(['plate' => 'ABC-2']);

    // Try to create 3rd vehicle (should fail)
    $this->assertTrue(hasReachedVehicleLimit());
}
```

## Migration Script Template

```sql
-- Add company_id to existing table
ALTER TABLE existing_table
ADD COLUMN company_id INT NOT NULL AFTER id,
ADD INDEX idx_company (company_id),
ADD FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE;

-- Set default company_id (if you have existing data)
UPDATE existing_table SET company_id = 1 WHERE company_id = 0;
```

## Performance Tips

1. **Always Index company_id**: Improves query performance dramatically
   ```sql
   CREATE INDEX idx_company_id ON table_name(company_id);
   ```

2. **Use Compound Indexes** for common queries:
   ```sql
   CREATE INDEX idx_company_date ON table_name(company_id, created_at);
   ```

3. **Cache Company Settings**: Load once per request
   ```php
   // Good: Load once in controller constructor
   private $companySettings;

   public function __construct() {
       $this->companySettings = getCurrentCompany();
   }
   ```

4. **Batch Operations**: When possible, use batch inserts
   ```php
   $this->db->query("INSERT INTO items (company_id, name) VALUES (:company_id, :name)");
   foreach ($items as $item) {
       $this->db->bind(':company_id', getCurrentCompanyId());
       $this->db->bind(':name', $item);
       $this->db->execute();
   }
   ```

## Troubleshooting

### Issue: "No company context"
**Cause**: company_id not in session
**Solution**: Ensure user login loads company via `loadCompanyIntoSession()`

### Issue: "Data from other companies visible"
**Cause**: Missing company filter in query
**Solution**: Add `WHERE " . companyFilter()` to all SELECT queries

### Issue: "Resource limit not enforced"
**Cause**: Missing middleware check
**Solution**: Add `CompanyMiddleware::checkResourceLimit()` before create operations

### Issue: "Super admin can't switch companies"
**Cause**: is_super_admin not set in session
**Solution**: Ensure login sets `$_SESSION['is_super_admin'] = true` for super admins

## Additional Resources

- [Multi-Tenant Improvements Documentation](MULTI_TENANT_IMPROVEMENTS.md)
- [Deployment Checklist](DEPLOYMENT_CHECKLIST.md)
- [Database Schema](../database/schema.sql)
- [API Documentation](API.md)

## Support

For questions or issues:
- Create an issue in the project repository
- Contact: dev@pakiparc.com
- Documentation: https://docs.pakiparc.com
