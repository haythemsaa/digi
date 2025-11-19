# Multi-Tenant Model Migration Guide

## Overview

This document provides a step-by-step guide for adding multi-tenant support to existing models that don't yet have it.

## Models Status

### ✅ Already Multi-Tenant (10 models)
- Vehicle.php
- Fuel.php
- Driver.php
- Maintenance.php
- Mission.php
- Tracking.php
- Financial.php
- Inventory.php
- Supplier.php
- User.php
- **Rental.php** ← Recently updated

### ⚠️  Needs Multi-Tenant Support (7 models)
1. Transport.php - Transport quotes, orders, client management
2. SmartDelivery.php - AI-powered delivery optimization
3. PassengerTransport.php - Taxi/VTC/bus operations
4. CashRegister.php - Cash register and operations
5. TCO.php - Total Cost of Ownership calculations
6. StockDocument.php - Advanced stock management
7. PurchaseRequest.php - Purchase request workflow

## Migration Pattern

Follow this standardized pattern for ALL model migrations:

### Step 1: Add Class Properties

```php
class YourModel extends Model {
    private $companyId;

    public function __construct() {
        parent::__construct();
        $this->companyId = getCurrentCompanyId();

        // Ensure company context exists
        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }
```

### Step 2: Add Helper Methods

```php
    /**
     * Get company filter for SQL queries
     */
    private function getCompanyFilter($tableAlias = '') {
        if (isSuperAdmin()) {
            return '1=1'; // No filter for super admins
        }
        $prefix = $tableAlias ? "{$tableAlias}." : '';
        return "{$prefix}company_id = :company_id";
    }

    /**
     * Bind company ID to query
     */
    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }
```

### Step 3: Update SELECT Queries

**Before:**
```php
public function getAll() {
    $this->db->query("SELECT * FROM table_name WHERE status = 'active'");
    return $this->db->fetchAll();
}
```

**After:**
```php
public function getAll() {
    $companyFilter = $this->getCompanyFilter('t');
    $this->db->query("SELECT * FROM table_name t WHERE t.status = 'active' AND {$companyFilter}");
    $this->bindCompanyId();
    return $this->db->fetchAll();
}
```

**With table alias:**
```php
public function getWithJoin() {
    $companyFilter = $this->getCompanyFilter('t');
    $sql = "SELECT t.*, v.name
            FROM table_name t
            LEFT JOIN vehicles v ON t.vehicle_id = v.id
            WHERE {$companyFilter}
            ORDER BY t.created_at DESC";
    $this->db->query($sql);
    $this->bindCompanyId();
    return $this->db->fetchAll();
}
```

### Step 4: Update INSERT Queries

**Before:**
```php
public function create($data) {
    $this->db->query("INSERT INTO table_name (field1, field2) VALUES (?, ?)");
    $this->db->bind(1, $data['field1']);
    $this->db->bind(2, $data['field2']);
    return $this->db->execute();
}
```

**After:**
```php
public function create($data) {
    $this->db->query("INSERT INTO table_name (company_id, field1, field2) VALUES (?, ?, ?)");
    $this->db->bind(1, $this->companyId);
    $this->db->bind(2, $data['field1']);
    $this->db->bind(3, $data['field2']);
    return $this->db->execute();
}
```

### Step 5: Update UPDATE Queries

**Before:**
```php
public function update($id, $data) {
    $this->db->query("UPDATE table_name SET field1 = ? WHERE id = ?");
    $this->db->bind(1, $data['field1']);
    $this->db->bind(2, $id);
    return $this->db->execute();
}
```

**After:**
```php
public function update($id, $data) {
    $companyFilter = $this->getCompanyFilter('');
    $this->db->query("UPDATE table_name SET field1 = ? WHERE id = ? AND {$companyFilter}");
    $this->db->bind(1, $data['field1']);
    $this->db->bind(2, $id);
    $this->bindCompanyId();
    return $this->db->execute();
}
```

### Step 6: Update DELETE Queries

**Before:**
```php
public function delete($id) {
    $this->db->query("DELETE FROM table_name WHERE id = ?");
    $this->db->bind(1, $id);
    return $this->db->execute();
}
```

**After:**
```php
public function delete($id) {
    $companyFilter = $this->getCompanyFilter('');
    $this->db->query("DELETE FROM table_name WHERE id = ? AND {$companyFilter}");
    $this->db->bind(1, $id);
    $this->bindCompanyId();
    return $this->db->execute();
}
```

## Special Cases

### Case 1: Dynamic WHERE Clauses with Filters

```php
public function getFiltered($filters = []) {
    $companyFilter = $this->getCompanyFilter('t');
    $sql = "SELECT * FROM table_name t WHERE {$companyFilter}";

    $params = [];
    if (!empty($filters['status'])) {
        $sql .= " AND t.status = ?";
        $params[] = $filters['status'];
    }

    $sql .= " ORDER BY t.created_at DESC";

    $this->db->query($sql);
    $this->bindCompanyId();

    foreach ($params as $i => $param) {
        $this->db->bind($i + 1, $param);
    }

    return $this->db->fetchAll();
}
```

### Case 2: Aggregate Queries (COUNT, SUM, etc.)

```php
public function getStats() {
    $companyFilter = $this->getCompanyFilter('');

    $this->db->query("SELECT COUNT(*) as total FROM table_name WHERE {$companyFilter}");
    $this->bindCompanyId();
    $result = $this->db->fetch();

    return $result['total'];
}
```

### Case 3: Subqueries

```php
public function getWithSubquery() {
    $companyFilter1 = $this->getCompanyFilter('t');
    $companyFilter2 = $this->getCompanyFilter('sub');

    $sql = "SELECT t.* FROM table_name t
            WHERE {$companyFilter1}
            AND t.id IN (
                SELECT sub.parent_id FROM subtable sub
                WHERE {$companyFilter2}
            )";

    $this->db->query($sql);
    $this->bindCompanyId();
    return $this->db->fetchAll();
}
```

**Note:** When using the same company_id binding multiple times in subqueries, the `bindCompanyId()` method will handle it automatically.

### Case 4: Number Generation (Invoice Numbers, etc.)

```php
private function generateNumber() {
    $companyFilter = $this->getCompanyFilter('');
    $prefix = 'INV-' . date('Y') . '-';

    $this->db->query("SELECT number FROM table_name
                     WHERE number LIKE ? AND {$companyFilter}
                     ORDER BY number DESC LIMIT 1");
    $this->db->bind(1, $prefix . '%');
    $this->bindCompanyId();
    $result = $this->db->fetch();

    if ($result) {
        $lastNumber = intval(substr($result['number'], -4));
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '0001';
    }

    return $prefix . $newNumber;
}
```

## Testing Checklist

After migrating a model, verify:

- [ ] All SELECT queries include company filter
- [ ] All INSERT queries include company_id
- [ ] All UPDATE queries filter by company_id
- [ ] All DELETE queries filter by company_id
- [ ] Subqueries are properly filtered
- [ ] Number generation is company-specific
- [ ] Aggregate queries are company-aware
- [ ] Super admin can see all data
- [ ] Regular users only see their company data
- [ ] Cross-company data access is prevented

## Quick Verification Script

Create a test script to verify multi-tenant isolation:

```php
<?php
// Test script: test_multi_tenant.php
require_once 'config/config.php';
require_once 'app/helpers/multi_tenant_helper.php';

// Simulate Company 1 context
$_SESSION['company_id'] = 1;
$model = new YourModel();
$data1 = $model->getAll();
echo "Company 1 records: " . count($data1) . "\n";

// Simulate Company 2 context
$_SESSION['company_id'] = 2;
$model = new YourModel();
$data2 = $model->getAll();
echo "Company 2 records: " . count($data2) . "\n";

// Verify isolation
if (count($data1) > 0 && count($data2) > 0) {
    $ids1 = array_column($data1, 'id');
    $ids2 = array_column($data2, 'id');
    $overlap = array_intersect($ids1, $ids2);

    if (empty($overlap)) {
        echo "✅ Data isolation verified!\n";
    } else {
        echo "❌ DATA LEAK DETECTED! Overlapping IDs: " . implode(',', $overlap) . "\n";
    }
}
```

## Migration Priority

Recommended order for migrating remaining models:

### Priority 1 (Core Business Logic)
1. **Transport.php** - Handles transport quotes and orders
2. **SmartDelivery.php** - AI delivery optimization (competitive advantage)

### Priority 2 (Operational Features)
3. **PassengerTransport.php** - Taxi/VTC operations
4. **CashRegister.php** - Financial operations

### Priority 3 (Supporting Features)
5. **TCO.php** - Cost calculations
6. **StockDocument.php** - Advanced stock features
7. **PurchaseRequest.php** - Procurement workflow

## Common Mistakes to Avoid

1. **Forgetting table aliases** - When using `getCompanyFilter('t')`, ensure the query uses `t.field` notation

2. **Wrong bind parameter position** - After adding `company_id` as first parameter in INSERT, all subsequent bind positions shift by 1

3. **Missing company filter in subqueries** - Subqueries MUST also filter by company_id

4. **Using global queries** - Statistics and dashboards must respect company context

5. **Not testing super admin** - Verify super admins can still see all data

## Rollback Plan

If issues arise after migration:

1. **Database Backup** - Always have a recent backup before migration
2. **Git Revert** - Use `git revert <commit>` to undo changes
3. **Temporary Disable** - Add `return '1=1';` to `getCompanyFilter()` to temporarily disable filtering

## Support

For questions or issues:
- Review existing migrated models (Vehicle.php, Fuel.php, Rental.php)
- Consult DEVELOPER_GUIDE.md
- Check multi_tenant_helper.php for available functions
- Test thoroughly in development environment

---

**Last Updated:** 2025-11-19
**Status:** In Progress (11/18 models complete)
