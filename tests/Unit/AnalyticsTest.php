<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/models/Analytics.php';

/**
 * Analytics Model Test
 */
class AnalyticsTest extends TestCase
{
    private $analytics;
    private $testCompanyId;

    protected function setUp(): void
    {
        $this->analytics = new Analytics();
        $this->testCompanyId = createTestCompany('Test Analytics Company');
    }

    public function testGetDashboardKPIs()
    {
        $kpis = $this->analytics->getDashboardKPIs($this->testCompanyId, 'month');

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('fleet', $kpis);
        $this->assertArrayHasKey('financial', $kpis);
        $this->assertArrayHasKey('operations', $kpis);
    }

    public function testGetFleetKPIs()
    {
        $dateFilter = "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $kpis = $this->analytics->getFleetKPIs($this->testCompanyId, $dateFilter);

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('total_vehicles', $kpis);
        $this->assertArrayHasKey('active_vehicles', $kpis);
        $this->assertArrayHasKey('vehicles_in_maintenance', $kpis);
    }

    public function testGetFinancialKPIs()
    {
        $dateFilter = "created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $kpis = $this->analytics->getFinancialKPIs($this->testCompanyId, $dateFilter);

        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('total_revenue', $kpis);
        $this->assertArrayHasKey('total_expenses', $kpis);
        $this->assertArrayHasKey('profit', $kpis);
    }

    public function testInvalidPeriod()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->analytics->getDashboardKPIs($this->testCompanyId, 'invalid_period');
    }
}
