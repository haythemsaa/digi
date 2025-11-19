<?php

/**
 * Carbon Tracking Model
 * Handles CO2 emissions tracking and CSR reporting
 *
 * @author DigiParc Team
 * @version 1.0
 */
class CarbonTracking
{
    private $db;
    private $emissionFactors;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->emissionFactors = require __DIR__ . '/../../config/emission_factors.php';
    }

    /**
     * Calculate CO2 emissions for a trip/mission
     *
     * @param array $data [distance, fuel_type, consumption]
     * @return float CO2 in kg
     */
    public function calculateEmissions($data)
    {
        $distance = (float)$data['distance']; // km
        $fuelType = $data['fuel_type'] ?? 'diesel';
        $consumption = (float)($data['consumption'] ?? 0); // liters

        // If consumption is provided, use it
        if ($consumption > 0) {
            $factor = $this->emissionFactors['fuel_types'][$fuelType] ?? 2.67;
            return $consumption * $factor;
        }

        // Otherwise, estimate based on vehicle category
        $category = $data['vehicle_category'] ?? 'medium_car';
        $factor = $this->emissionFactors['vehicle_categories'][$category] ?? 0.140;

        return $distance * $factor;
    }

    /**
     * Get carbon footprint for company
     *
     * @param int $companyId
     * @param string $period (month, year)
     * @return array
     */
    public function getCompanyFootprint($companyId, $period = 'month')
    {
        $dateFilter = $this->getDateFilter($period);

        // Get total emissions from missions
        $sql = "SELECT
                    SUM(m.distance) as total_distance,
                    SUM(fc.quantity) as total_fuel,
                    v.fuel_type,
                    v.vehicle_type,
                    COUNT(DISTINCT m.id) as total_missions,
                    COUNT(DISTINCT v.id) as vehicles_used
                FROM missions m
                JOIN vehicles v ON m.vehicle_id = v.id
                LEFT JOIN fuel_consumption fc ON m.id = fc.mission_id
                WHERE m.company_id = :company_id
                AND m.created_at >= $dateFilter
                GROUP BY v.fuel_type, v.vehicle_type";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalCO2 = 0;
        $totalDistance = 0;
        $totalFuel = 0;
        $breakdown = [];

        foreach ($results as $row) {
            $fuelType = $row['fuel_type'] ?? 'diesel';
            $distance = (float)$row['total_distance'];
            $fuel = (float)$row['total_fuel'];

            $co2 = $this->calculateEmissions([
                'distance' => $distance,
                'fuel_type' => $fuelType,
                'consumption' => $fuel,
                'vehicle_category' => $row['vehicle_type']
            ]);

            $totalCO2 += $co2;
            $totalDistance += $distance;
            $totalFuel += $fuel;

            $breakdown[$fuelType] = [
                'distance' => $distance,
                'fuel' => $fuel,
                'co2' => $co2,
                'missions' => (int)$row['total_missions']
            ];
        }

        return [
            'total_co2' => round($totalCO2, 2), // kg
            'total_co2_tons' => round($totalCO2 / 1000, 3), // tons
            'total_distance' => $totalDistance, // km
            'total_fuel' => $totalFuel, // liters
            'avg_co2_per_km' => $totalDistance > 0 ? round($totalCO2 / $totalDistance, 3) : 0,
            'breakdown' => $breakdown,
            'compensation_cost' => round(($totalCO2 / 1000) * $this->emissionFactors['compensation']['price_per_ton'], 2)
        ];
    }

    /**
     * Get carbon trend (monthly evolution)
     */
    public function getCarbonTrend($companyId, $months = 12)
    {
        $sql = "SELECT
                    DATE_FORMAT(m.created_at, '%Y-%m') as month,
                    SUM(m.distance) as total_distance,
                    SUM(fc.quantity) as total_fuel,
                    v.fuel_type
                FROM missions m
                JOIN vehicles v ON m.vehicle_id = v.id
                LEFT JOIN fuel_consumption fc ON m.id = fc.mission_id
                WHERE m.company_id = :company_id
                AND m.created_at >= DATE_SUB(NOW(), INTERVAL :months MONTH)
                GROUP BY month, v.fuel_type
                ORDER BY month ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':months', $months, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $trend = [];
        foreach ($results as $row) {
            $month = $row['month'];
            $co2 = $this->calculateEmissions([
                'distance' => $row['total_distance'],
                'fuel_type' => $row['fuel_type'],
                'consumption' => $row['total_fuel']
            ]);

            if (!isset($trend[$month])) {
                $trend[$month] = [
                    'month' => $month,
                    'co2' => 0,
                    'distance' => 0
                ];
            }

            $trend[$month]['co2'] += $co2;
            $trend[$month]['distance'] += $row['total_distance'];
        }

        return array_values($trend);
    }

    /**
     * Get vehicle carbon performance
     */
    public function getVehiclePerformance($companyId, $limit = 10)
    {
        $sql = "SELECT
                    v.id,
                    v.registration_number,
                    v.make,
                    v.model,
                    v.fuel_type,
                    v.vehicle_type,
                    SUM(m.distance) as total_distance,
                    SUM(fc.quantity) as total_fuel,
                    COUNT(m.id) as total_missions
                FROM vehicles v
                LEFT JOIN missions m ON v.id = m.vehicle_id
                LEFT JOIN fuel_consumption fc ON m.id = fc.mission_id
                WHERE v.company_id = :company_id
                GROUP BY v.id
                HAVING total_distance > 0
                ORDER BY total_distance DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vehicles as &$vehicle) {
            $co2 = $this->calculateEmissions([
                'distance' => $vehicle['total_distance'],
                'fuel_type' => $vehicle['fuel_type'],
                'consumption' => $vehicle['total_fuel'],
                'vehicle_category' => $vehicle['vehicle_type']
            ]);

            $vehicle['total_co2'] = round($co2, 2);
            $vehicle['co2_per_km'] = round($co2 / $vehicle['total_distance'], 3);
            $vehicle['eco_score'] = $this->calculateEcoScore($vehicle['co2_per_km'], $vehicle['vehicle_type']);
        }

        // Sort by eco score
        usort($vehicles, function($a, $b) {
            return $b['eco_score'] - $a['eco_score'];
        });

        return $vehicles;
    }

    /**
     * Get driver carbon performance
     */
    public function getDriverPerformance($companyId, $limit = 10)
    {
        $sql = "SELECT
                    d.id,
                    d.first_name,
                    d.last_name,
                    SUM(m.distance) as total_distance,
                    SUM(fc.quantity) as total_fuel,
                    COUNT(m.id) as total_missions,
                    AVG(fc.consumption) as avg_consumption
                FROM drivers d
                JOIN missions m ON d.id = m.driver_id
                LEFT JOIN fuel_consumption fc ON m.id = fc.mission_id
                WHERE d.company_id = :company_id
                GROUP BY d.id
                HAVING total_distance > 0
                ORDER BY total_distance DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($drivers as &$driver) {
            // Estimate CO2 (assuming average diesel vehicle)
            $co2 = $this->calculateEmissions([
                'distance' => $driver['total_distance'],
                'fuel_type' => 'diesel',
                'consumption' => $driver['total_fuel']
            ]);

            $driver['total_co2'] = round($co2, 2);
            $driver['co2_per_km'] = round($co2 / $driver['total_distance'], 3);

            // Eco-driving score (0-100)
            $driver['eco_score'] = $this->calculateDriverEcoScore($driver['avg_consumption']);
        }

        // Sort by eco score
        usort($drivers, function($a, $b) {
            return $b['eco_score'] - $a['eco_score'];
        });

        return $drivers;
    }

    /**
     * Calculate eco score (0-100)
     */
    private function calculateEcoScore($co2PerKm, $vehicleType)
    {
        $benchmark = $this->emissionFactors['vehicle_categories'][$vehicleType] ?? 0.140;

        // Perfect score if 30% better than benchmark
        $perfect = $benchmark * 0.70;
        // Zero score if 50% worse than benchmark
        $worst = $benchmark * 1.50;

        if ($co2PerKm <= $perfect) {
            return 100;
        } elseif ($co2PerKm >= $worst) {
            return 0;
        }

        // Linear scale between perfect and worst
        return round(100 - (($co2PerKm - $perfect) / ($worst - $perfect)) * 100);
    }

    /**
     * Calculate driver eco score based on consumption
     */
    private function calculateDriverEcoScore($avgConsumption)
    {
        // Benchmark: 7.5 L/100km for average vehicle
        $benchmark = 7.5;
        $perfect = 5.0; // Excellent eco-driving
        $worst = 12.0;  // Poor driving

        if ($avgConsumption <= $perfect) {
            return 100;
        } elseif ($avgConsumption >= $worst) {
            return 0;
        }

        return round(100 - (($avgConsumption - $perfect) / ($worst - $perfect)) * 100);
    }

    /**
     * Get eco-driving recommendations
     */
    public function getEcoRecommendations($companyId)
    {
        $footprint = $this->getCompanyFootprint($companyId, 'month');

        $recommendations = [];

        // Calculate potential savings for each tip
        foreach ($this->emissionFactors['eco_driving_tips'] as $key => $tip) {
            $reduction = $footprint['total_co2'] * ($tip['reduction_percent'] / 100);
            $costSaving = ($footprint['total_fuel'] * ($tip['reduction_percent'] / 100)) * 1.80; // Assuming 1.80€/L

            $recommendations[] = [
                'tip' => $tip['name'],
                'description' => $tip['description'],
                'reduction_percent' => $tip['reduction_percent'],
                'co2_reduction' => round($reduction, 2),
                'cost_saving' => round($costSaving, 2)
            ];
        }

        return $recommendations;
    }

    /**
     * Generate CSR report data
     */
    public function generateCSRReport($companyId, $year = null)
    {
        $year = $year ?? date('Y');

        $sql = "SELECT
                    SUM(m.distance) as total_distance,
                    SUM(fc.quantity) as total_fuel,
                    COUNT(DISTINCT m.id) as total_missions,
                    COUNT(DISTINCT v.id) as vehicles_used,
                    COUNT(DISTINCT d.id) as drivers_count
                FROM missions m
                JOIN vehicles v ON m.vehicle_id = v.id
                LEFT JOIN drivers d ON m.driver_id = d.id
                LEFT JOIN fuel_consumption fc ON m.id = fc.mission_id
                WHERE m.company_id = :company_id
                AND YEAR(m.created_at) = :year";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':year', $year);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $footprint = $this->getCompanyFootprint($companyId, 'year');
        $trend = $this->getCarbonTrend($companyId, 12);

        // Calculate year-over-year change
        $previousYear = $year - 1;
        $previousFootprint = $this->getCompanyFootprint($companyId, 'year'); // Would need to filter by year

        return [
            'year' => $year,
            'statistics' => $data,
            'carbon_footprint' => $footprint,
            'trend' => $trend,
            'eco_recommendations' => $this->getEcoRecommendations($companyId),
            'top_performers' => [
                'vehicles' => $this->getVehiclePerformance($companyId, 5),
                'drivers' => $this->getDriverPerformance($companyId, 5)
            ],
            'goals' => $this->emissionFactors['csr_goals']
        ];
    }

    /**
     * Helper: Get date filter
     */
    private function getDateFilter($period)
    {
        return match($period) {
            'day' => 'DATE(NOW())',
            'week' => 'DATE_SUB(NOW(), INTERVAL 7 DAY)',
            'month' => 'DATE_SUB(NOW(), INTERVAL 30 DAY)',
            'quarter' => 'DATE_SUB(NOW(), INTERVAL 90 DAY)',
            'year' => 'DATE_SUB(NOW(), INTERVAL 365 DAY)',
            default => 'DATE_SUB(NOW(), INTERVAL 30 DAY)'
        };
    }
}
