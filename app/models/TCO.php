<?php
/**
 * TCO (Total Cost of Ownership) Model
 * Manages vehicle TCO calculations and configurations
 */

class TCO extends Model {

    /**
     * Get all TCO configurations
     */
    public function getAllConfigurations() {
        $this->db->query("SELECT tc.*, vt.name as vehicle_type_name
                         FROM tco_configurations tc
                         LEFT JOIN vehicle_types vt ON tc.vehicle_type_id = vt.id
                         WHERE tc.is_active = 1
                         ORDER BY tc.created_at DESC");
        return $this->db->resultSet();
    }

    /**
     * Get configuration by ID
     */
    public function getConfigurationById($id) {
        $this->db->query("SELECT tc.*, vt.name as vehicle_type_name
                         FROM tco_configurations tc
                         LEFT JOIN vehicle_types vt ON tc.vehicle_type_id = vt.id
                         WHERE tc.id = ?");
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    /**
     * Get configuration by vehicle type
     */
    public function getConfigurationByVehicleType($vehicleTypeId) {
        $this->db->query("SELECT * FROM tco_configurations
                         WHERE vehicle_type_id = ? AND is_active = 1
                         ORDER BY created_at DESC LIMIT 1");
        $this->db->bind(1, $vehicleTypeId);
        return $this->db->single();
    }

    /**
     * Create TCO configuration
     */
    public function createConfiguration($data) {
        $this->db->query("INSERT INTO tco_configurations
                         (vehicle_type_id, name,
                          depreciation_rate, annual_depreciation,
                          fuel_cost_per_km, maintenance_cost_per_km,
                          insurance_annual, tax_annual, parking_annual,
                          driver_salary_annual, admin_cost_annual,
                          average_annual_km, expected_lifetime_years,
                          residual_value_percentage, is_active)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $data['vehicle_type_id'] ?? null);
        $this->db->bind(2, $data['name']);
        $this->db->bind(3, $data['depreciation_rate'] ?? 0);
        $this->db->bind(4, $data['annual_depreciation'] ?? 0);
        $this->db->bind(5, $data['fuel_cost_per_km'] ?? 0);
        $this->db->bind(6, $data['maintenance_cost_per_km'] ?? 0);
        $this->db->bind(7, $data['insurance_annual'] ?? 0);
        $this->db->bind(8, $data['tax_annual'] ?? 0);
        $this->db->bind(9, $data['parking_annual'] ?? 0);
        $this->db->bind(10, $data['driver_salary_annual'] ?? 0);
        $this->db->bind(11, $data['admin_cost_annual'] ?? 0);
        $this->db->bind(12, $data['average_annual_km'] ?? 15000);
        $this->db->bind(13, $data['expected_lifetime_years'] ?? 5);
        $this->db->bind(14, $data['residual_value_percentage'] ?? 20);
        $this->db->bind(15, $data['is_active'] ?? 1);

        return $this->db->execute();
    }

    /**
     * Update TCO configuration
     */
    public function updateConfiguration($id, $data) {
        $this->db->query("UPDATE tco_configurations SET
                         vehicle_type_id = ?, name = ?,
                         depreciation_rate = ?, annual_depreciation = ?,
                         fuel_cost_per_km = ?, maintenance_cost_per_km = ?,
                         insurance_annual = ?, tax_annual = ?, parking_annual = ?,
                         driver_salary_annual = ?, admin_cost_annual = ?,
                         average_annual_km = ?, expected_lifetime_years = ?,
                         residual_value_percentage = ?, is_active = ?
                         WHERE id = ?");

        $this->db->bind(1, $data['vehicle_type_id'] ?? null);
        $this->db->bind(2, $data['name']);
        $this->db->bind(3, $data['depreciation_rate'] ?? 0);
        $this->db->bind(4, $data['annual_depreciation'] ?? 0);
        $this->db->bind(5, $data['fuel_cost_per_km'] ?? 0);
        $this->db->bind(6, $data['maintenance_cost_per_km'] ?? 0);
        $this->db->bind(7, $data['insurance_annual'] ?? 0);
        $this->db->bind(8, $data['tax_annual'] ?? 0);
        $this->db->bind(9, $data['parking_annual'] ?? 0);
        $this->db->bind(10, $data['driver_salary_annual'] ?? 0);
        $this->db->bind(11, $data['admin_cost_annual'] ?? 0);
        $this->db->bind(12, $data['average_annual_km'] ?? 15000);
        $this->db->bind(13, $data['expected_lifetime_years'] ?? 5);
        $this->db->bind(14, $data['residual_value_percentage'] ?? 20);
        $this->db->bind(15, $data['is_active'] ?? 1);
        $this->db->bind(16, $id);

        return $this->db->execute();
    }

    /**
     * Delete configuration
     */
    public function deleteConfiguration($id) {
        $this->db->query("UPDATE tco_configurations SET is_active = 0 WHERE id = ?");
        $this->db->bind(1, $id);
        return $this->db->execute();
    }

    // ==================== TCO CALCULATIONS ====================

    /**
     * Get all TCO calculations
     */
    public function getAllCalculations($filters = []) {
        $sql = "SELECT tcalc.*,
                       v.registration_number, v.make, v.model,
                       tconf.name as config_name,
                       CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                FROM tco_calculations tcalc
                LEFT JOIN vehicles v ON tcalc.vehicle_id = v.id
                LEFT JOIN tco_configurations tconf ON tcalc.configuration_id = tconf.id
                LEFT JOIN users u ON tcalc.created_by = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['vehicle_id'])) {
            $sql .= " AND tcalc.vehicle_id = ?";
            $params[] = $filters['vehicle_id'];
        }

        $sql .= " ORDER BY tcalc.calculation_date DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get calculation by ID
     */
    public function getCalculationById($id) {
        $this->db->query("SELECT tcalc.*,
                                 v.registration_number, v.make, v.model, v.year,
                                 tconf.name as config_name,
                                 CONCAT(u.first_name, ' ', u.last_name) as created_by_name
                          FROM tco_calculations tcalc
                          LEFT JOIN vehicles v ON tcalc.vehicle_id = v.id
                          LEFT JOIN tco_configurations tconf ON tcalc.configuration_id = tconf.id
                          LEFT JOIN users u ON tcalc.created_by = u.id
                          WHERE tcalc.id = ?");
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    /**
     * Calculate TCO for a vehicle
     */
    public function calculateTCO($vehicleId, $configurationId = null, $customParams = []) {
        // Get vehicle details
        $this->db->query("SELECT * FROM vehicles WHERE id = ?");
        $this->db->bind(1, $vehicleId);
        $vehicle = $this->db->single();

        if (!$vehicle) {
            return false;
        }

        // Get configuration
        if ($configurationId) {
            $config = $this->getConfigurationById($configurationId);
        } else {
            $config = $this->getConfigurationByVehicleType($vehicle['vehicle_type_id']);
        }

        if (!$config) {
            return ['error' => 'No TCO configuration found'];
        }

        // Merge custom parameters with config
        $params = array_merge($config, $customParams);

        // Get actual vehicle costs from database
        $actualCosts = $this->getActualVehicleCosts($vehicleId);

        // Purchase price
        $purchasePrice = floatval($vehicle['purchase_price'] ?? 0);

        // Depreciation
        $lifetimeYears = intval($params['expected_lifetime_years']);
        $residualValuePercentage = floatval($params['residual_value_percentage']);
        $residualValue = $purchasePrice * ($residualValuePercentage / 100);
        $totalDepreciation = $purchasePrice - $residualValue;
        $annualDepreciation = $totalDepreciation / $lifetimeYears;

        // Fixed annual costs
        $insuranceAnnual = floatval($params['insurance_annual']);
        $taxAnnual = floatval($params['tax_annual']);
        $parkingAnnual = floatval($params['parking_annual']);
        $driverSalaryAnnual = floatval($params['driver_salary_annual']);
        $adminCostAnnual = floatval($params['admin_cost_annual']);

        $totalFixedAnnual = $insuranceAnnual + $taxAnnual + $parkingAnnual +
                           $driverSalaryAnnual + $adminCostAnnual + $annualDepreciation;

        // Variable costs per km
        $fuelCostPerKm = floatval($params['fuel_cost_per_km']);
        $maintenanceCostPerKm = floatval($params['maintenance_cost_per_km']);
        $totalVariableCostPerKm = $fuelCostPerKm + $maintenanceCostPerKm;

        // Annual calculations
        $averageAnnualKm = floatval($params['average_annual_km']);
        $totalVariableAnnual = $totalVariableCostPerKm * $averageAnnualKm;
        $totalAnnualCost = $totalFixedAnnual + $totalVariableAnnual;

        // Lifetime calculations
        $totalLifetimeKm = $averageAnnualKm * $lifetimeYears;
        $totalLifetimeCost = ($totalAnnualCost * $lifetimeYears) + $purchasePrice - $residualValue;

        // Cost per km
        $costPerKm = $totalLifetimeCost / $totalLifetimeKm;

        // Monthly cost
        $monthlyCost = $totalAnnualCost / 12;

        // Prepare calculation data
        $calculation = [
            'vehicle_id' => $vehicleId,
            'configuration_id' => $config['id'],
            'calculation_date' => date('Y-m-d'),
            'purchase_price' => $purchasePrice,
            'residual_value' => $residualValue,
            'depreciation_total' => $totalDepreciation,
            'depreciation_annual' => $annualDepreciation,
            'fuel_cost_annual' => $fuelCostPerKm * $averageAnnualKm,
            'maintenance_cost_annual' => $maintenanceCostPerKm * $averageAnnualKm,
            'insurance_annual' => $insuranceAnnual,
            'tax_annual' => $taxAnnual,
            'parking_annual' => $parkingAnnual,
            'driver_salary_annual' => $driverSalaryAnnual,
            'admin_cost_annual' => $adminCostAnnual,
            'total_fixed_annual' => $totalFixedAnnual,
            'total_variable_annual' => $totalVariableAnnual,
            'total_annual_cost' => $totalAnnualCost,
            'total_lifetime_cost' => $totalLifetimeCost,
            'cost_per_km' => $costPerKm,
            'monthly_cost' => $monthlyCost,
            'lifetime_years' => $lifetimeYears,
            'annual_km' => $averageAnnualKm,
            'lifetime_km' => $totalLifetimeKm,
            'actual_fuel_cost' => $actualCosts['fuel_cost'],
            'actual_maintenance_cost' => $actualCosts['maintenance_cost'],
            'actual_total_cost' => $actualCosts['total_cost']
        ];

        return $calculation;
    }

    /**
     * Get actual vehicle costs from historical data
     */
    private function getActualVehicleCosts($vehicleId) {
        // Get fuel costs from fuel logs
        $this->db->query("SELECT SUM(total_cost) as fuel_cost
                         FROM fuel_logs
                         WHERE vehicle_id = ?");
        $this->db->bind(1, $vehicleId);
        $fuelResult = $this->db->single();
        $fuelCost = floatval($fuelResult['fuel_cost'] ?? 0);

        // Get maintenance costs from work orders
        $this->db->query("SELECT SUM(total_amount) as maintenance_cost
                         FROM work_orders
                         WHERE vehicle_id = ? AND status = 'completed'");
        $this->db->bind(1, $vehicleId);
        $maintenanceResult = $this->db->single();
        $maintenanceCost = floatval($maintenanceResult['maintenance_cost'] ?? 0);

        // Get total expenses
        $this->db->query("SELECT SUM(amount) as expense_total
                         FROM expenses
                         WHERE vehicle_id = ?");
        $this->db->bind(1, $vehicleId);
        $expenseResult = $this->db->single();
        $expenseTotal = floatval($expenseResult['expense_total'] ?? 0);

        return [
            'fuel_cost' => $fuelCost,
            'maintenance_cost' => $maintenanceCost,
            'total_cost' => $fuelCost + $maintenanceCost + $expenseTotal
        ];
    }

    /**
     * Save TCO calculation
     */
    public function saveCalculation($data) {
        $this->db->query("INSERT INTO tco_calculations
                         (vehicle_id, configuration_id, calculation_date,
                          purchase_price, residual_value, depreciation_total, depreciation_annual,
                          fuel_cost_annual, maintenance_cost_annual,
                          insurance_annual, tax_annual, parking_annual,
                          driver_salary_annual, admin_cost_annual,
                          total_fixed_annual, total_variable_annual, total_annual_cost,
                          total_lifetime_cost, cost_per_km, monthly_cost,
                          lifetime_years, annual_km, lifetime_km,
                          actual_fuel_cost, actual_maintenance_cost, actual_total_cost,
                          created_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $data['vehicle_id']);
        $this->db->bind(2, $data['configuration_id']);
        $this->db->bind(3, $data['calculation_date']);
        $this->db->bind(4, $data['purchase_price']);
        $this->db->bind(5, $data['residual_value']);
        $this->db->bind(6, $data['depreciation_total']);
        $this->db->bind(7, $data['depreciation_annual']);
        $this->db->bind(8, $data['fuel_cost_annual']);
        $this->db->bind(9, $data['maintenance_cost_annual']);
        $this->db->bind(10, $data['insurance_annual']);
        $this->db->bind(11, $data['tax_annual']);
        $this->db->bind(12, $data['parking_annual']);
        $this->db->bind(13, $data['driver_salary_annual']);
        $this->db->bind(14, $data['admin_cost_annual']);
        $this->db->bind(15, $data['total_fixed_annual']);
        $this->db->bind(16, $data['total_variable_annual']);
        $this->db->bind(17, $data['total_annual_cost']);
        $this->db->bind(18, $data['total_lifetime_cost']);
        $this->db->bind(19, $data['cost_per_km']);
        $this->db->bind(20, $data['monthly_cost']);
        $this->db->bind(21, $data['lifetime_years']);
        $this->db->bind(22, $data['annual_km']);
        $this->db->bind(23, $data['lifetime_km']);
        $this->db->bind(24, $data['actual_fuel_cost']);
        $this->db->bind(25, $data['actual_maintenance_cost']);
        $this->db->bind(26, $data['actual_total_cost']);
        $this->db->bind(27, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Compare TCO across multiple vehicles
     */
    public function compareTCO($vehicleIds) {
        $comparisons = [];

        foreach ($vehicleIds as $vehicleId) {
            $tco = $this->calculateTCO($vehicleId);
            if ($tco && !isset($tco['error'])) {
                $comparisons[] = $tco;
            }
        }

        return $comparisons;
    }

    /**
     * Get TCO summary statistics
     */
    public function getTCOStats() {
        $stats = [];

        // Average cost per km across fleet
        $this->db->query("SELECT AVG(cost_per_km) as avg_cost_per_km
                         FROM tco_calculations
                         WHERE calculation_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)");
        $result = $this->db->single();
        $stats['avg_cost_per_km'] = $result['avg_cost_per_km'] ?? 0;

        // Total annual fleet cost
        $this->db->query("SELECT SUM(total_annual_cost) as total_annual_cost
                         FROM tco_calculations tc
                         INNER JOIN (
                             SELECT vehicle_id, MAX(calculation_date) as max_date
                             FROM tco_calculations
                             GROUP BY vehicle_id
                         ) latest ON tc.vehicle_id = latest.vehicle_id
                         AND tc.calculation_date = latest.max_date");
        $result = $this->db->single();
        $stats['total_annual_cost'] = $result['total_annual_cost'] ?? 0;

        // Number of vehicles with TCO calculations
        $this->db->query("SELECT COUNT(DISTINCT vehicle_id) as count FROM tco_calculations");
        $result = $this->db->single();
        $stats['vehicles_analyzed'] = $result['count'];

        return $stats;
    }

    /**
     * Get fleet TCO report
     */
    public function getFleetTCOReport() {
        $this->db->query("SELECT
                             tc.vehicle_id,
                             v.registration_number, v.make, v.model,
                             tc.cost_per_km,
                             tc.monthly_cost,
                             tc.total_annual_cost,
                             tc.actual_fuel_cost,
                             tc.actual_maintenance_cost
                          FROM tco_calculations tc
                          INNER JOIN vehicles v ON tc.vehicle_id = v.id
                          INNER JOIN (
                              SELECT vehicle_id, MAX(calculation_date) as max_date
                              FROM tco_calculations
                              GROUP BY vehicle_id
                          ) latest ON tc.vehicle_id = latest.vehicle_id
                          AND tc.calculation_date = latest.max_date
                          ORDER BY tc.cost_per_km DESC");

        return $this->db->resultSet();
    }
}
