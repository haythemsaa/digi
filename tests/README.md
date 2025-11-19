# DigiParc Tests

This directory contains unit and integration tests for the DigiParc Fleet Management System.

## Running Tests

### Prerequisites

1. Install PHPUnit via Composer:
```bash
composer require --dev phpunit/phpunit
```

2. Create test database:
```bash
mysql -u root -p -e "CREATE DATABASE digiparc_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p digiparc_test < database/schema.sql
```

### Run All Tests

```bash
vendor/bin/phpunit
```

### Run Specific Test Suite

```bash
# Unit tests only
vendor/bin/phpunit --testsuite Unit

# Integration tests only
vendor/bin/phpunit --testsuite Integration
```

### Run Specific Test File

```bash
vendor/bin/phpunit tests/Unit/AnalyticsTest.php
```

### Run with Coverage Report

```bash
vendor/bin/phpunit --coverage-html coverage
```

## Test Structure

```
tests/
├── Unit/                      # Unit tests (isolated, fast)
│   ├── AnalyticsTest.php     # Analytics model tests
│   ├── CarbonTrackingTest.php # Carbon tracking tests
│   └── ...
├── Integration/               # Integration tests (API, DB)
│   ├── APITest.php           # REST API endpoint tests
│   └── ...
├── bootstrap.php              # Test setup & teardown
└── README.md                  # This file
```

## Writing Tests

### Unit Test Example

```php
<?php

use PHPUnit\Framework\TestCase;

class MyModelTest extends TestCase
{
    private $model;

    protected function setUp(): void
    {
        $this->model = new MyModel();
    }

    public function testSomething()
    {
        $result = $this->model->doSomething();
        $this->assertTrue($result);
    }
}
```

### Integration Test Example

```php
<?php

use PHPUnit\Framework\TestCase;

class MyAPITest extends TestCase
{
    public function testEndpoint()
    {
        $response = $this->makeRequest('GET', '/api/v1/endpoint');
        $this->assertEquals(200, $response['code']);
    }
}
```

## Test Coverage

Current test coverage:

- ✅ Analytics Model (5 tests)
- ✅ Carbon Tracking Model (4 tests)
- ✅ API Authentication (5 tests)
- ✅ Rate Limiting (1 test)
- ⏳ Alert System (planned)
- ⏳ Notification Service (planned)
- ⏳ GDPR Compliance (planned)
- ⏳ Backup Service (planned)

## Continuous Integration

Tests should be run automatically on:
- Every commit (pre-commit hook)
- Every pull request (GitHub Actions)
- Before deployment (deployment script)

Example GitHub Actions workflow:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit
```

## Notes

- Tests run against a separate `digiparc_test` database
- Test data is automatically cleaned up after each test
- Helper functions available in `bootstrap.php`
- Use `createTestCompany()` to create test companies
- Use `cleanupTestData()` to manually clean test data
