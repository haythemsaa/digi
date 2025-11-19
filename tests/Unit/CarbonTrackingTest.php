<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/models/CarbonTracking.php';

/**
 * Carbon Tracking Model Test
 */
class CarbonTrackingTest extends TestCase
{
    private $carbonTracking;

    protected function setUp(): void
    {
        $this->carbonTracking = new CarbonTracking();
    }

    public function testCalculateEmissionsWithConsumption()
    {
        $data = [
            'distance' => 100,
            'fuel_type' => 'diesel',
            'consumption' => 7.5 // liters
        ];

        $emissions = $this->carbonTracking->calculateEmissions($data);

        // 7.5 liters * 2.67 kg CO2/liter = 20.025 kg
        $this->assertEqualsWithDelta(20.025, $emissions, 0.01);
    }

    public function testCalculateEmissionsWithoutConsumption()
    {
        $data = [
            'distance' => 100,
            'fuel_type' => 'diesel',
            'vehicle_category' => 'medium_car'
        ];

        $emissions = $this->carbonTracking->calculateEmissions($data);

        // 100 km * 0.140 kg/km = 14 kg
        $this->assertEqualsWithDelta(14.0, $emissions, 0.01);
    }

    public function testCalculateEcoScore()
    {
        // Excellent performance (low emissions)
        $excellentScore = $this->carbonTracking->calculateEcoScore(100, 10);
        $this->assertGreaterThanOrEqual(80, $excellentScore);

        // Poor performance (high emissions)
        $poorScore = $this->carbonTracking->calculateEcoScore(100, 30);
        $this->assertLessThan(50, $poorScore);
    }

    public function testGetRecommendations()
    {
        $testCompanyId = createTestCompany('Test Carbon Company');
        $recommendations = $this->carbonTracking->getRecommendations($testCompanyId);

        $this->assertIsArray($recommendations);
        $this->assertNotEmpty($recommendations);

        foreach ($recommendations as $rec) {
            $this->assertArrayHasKey('tip', $rec);
            $this->assertArrayHasKey('impact', $rec);
        }
    }
}
