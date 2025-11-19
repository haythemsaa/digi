<?php

/**
 * Analytics Model
 * Handles advanced analytics and KPI calculations for the dashboard
 *
 * @author Pakiparc Team
 * @version 1.0
 */
class Analytics
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get comprehensive dashboard KPIs for a company
     *
     * @param int $companyId Company ID
     * @param string $period Period (day, week, month, year)
     * @return array KPIs
     */
    public function getDashboardKPIs($companyId, $period = 'month')
    {
        $dateFilter = $this->getDateFilter($period);

        return [
            'fleet' => $this->getFleetKPIs($companyId, $dateFilter),
            'financial' => $this->getFinancialKPIs($companyId, $dateFilter),
            'operations' => $this->getOperationsKPIs($companyId, $dateFilter),
            'maintenance' => $this->getMaintenanceKPIs($companyId, $dateFilter),
            'drivers' => $this->getDriversKPIs($companyId, $dateFilter),
            'alerts' => $this->getAlertsKPIs($companyId)
        ];
    }

    /**
     * Get Fleet KPIs
     */
    private function getFleetKPIs($companyId, $dateFilter)
    {
        // Total vehicles
        $sql = "SELECT COUNT(*) as total_vehicles,
                       COUNT(CASE WHEN status = 'active' THEN 1 END) as active_vehicles,
                       COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as in_maintenance,
                       AVG(YEAR(CURDATE()) - YEAR(purchase_date)) as avg_age
                FROM vehicles
                WHERE company_id = :company_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $fleet = $stmt->fetch(PDO::FETCH_ASSOC);

        // Utilization rate (km driven vs capacity)
        $sql = "SELECT
                    SUM(m.distance) as total_distance,
                    COUNT(DISTINCT m.vehicle_id) as vehicles_used
                FROM missions m
                WHERE m.company_id = :company_id
                AND m.created_at >= $dateFilter";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $usage = $stmt->fetch(PDO::FETCH_ASSOC);

        $utilizationRate = $fleet['active_vehicles'] > 0
            ? ($usage['vehicles_used'] / $fleet['active_vehicles']) * 100
            : 0;

        return [
            'total_vehicles' => (int)$fleet['total_vehicles'],
            'active_vehicles' => (int)$fleet['active_vehicles'],
            'in_maintenance' => (int)$fleet['in_maintenance'],
            'avg_age' => round($fleet['avg_age'], 1),
            'utilization_rate' => round($utilizationRate, 1),
            'total_distance' => (float)$usage['total_distance'] ?? 0
        ];
    }

    /**
     * Get Financial KPIs
     */
    private function getFinancialKPIs($companyId, $dateFilter)
    {
        // Total costs
        $sql = "SELECT
                    SUM(CASE WHEN type = 'fuel' THEN amount ELSE 0 END) as fuel_costs,
                    SUM(CASE WHEN type = 'maintenance' THEN amount ELSE 0 END) as maintenance_costs,
                    SUM(CASE WHEN type = 'insurance' THEN amount ELSE 0 END) as insurance_costs,
                    SUM(amount) as total_costs
                FROM expenses
                WHERE company_id = :company_id
                AND created_at >= $dateFilter";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $costs = $stmt->fetch(PDO::FETCH_ASSOC);

        // Total revenue
        $sql = "SELECT SUM(amount) as total_revenue
                FROM invoices
                WHERE company_id = :company_id
                AND status = 'paid'
                AND created_at >= $dateFilter";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $revenue = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalRevenue = (float)($revenue['total_revenue'] ?? 0);
        $totalCosts = (float)($costs['total_costs'] ?? 0);
        $profit = $totalRevenue - $totalCosts;
        $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_costs' => $totalCosts,
            'fuel_costs' => (float)($costs['fuel_costs'] ?? 0),
            'maintenance_costs' => (float)($costs['maintenance_costs'] ?? 0),
            'insurance_costs' => (float)($costs['insurance_costs'] ?? 0),
            'profit' => $profit,
            'profit_margin' => round($profitMargin, 2)
        ];
    }

    /**
     * Get Operations KPIs
     */
    private function getOperationsKPIs($companyId, $dateFilter)
    {
        $sql = "SELECT
                    COUNT(*) as total_missions,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_missions,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_missions,
                    AVG(CASE
                        WHEN status = 'completed' AND end_time IS NOT NULL
                        THEN TIMESTAMPDIFF(MINUTE, start_time, end_time)
                    END) as avg_mission_duration,
                    SUM(distance) as total_distance
                FROM missions
                WHERE company_id = :company_id
                AND created_at >= $dateFilter";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $operations = $stmt->fetch(PDO::FETCH_ASSOC);

        $completionRate = $operations['total_missions'] > 0
            ? ($operations['completed_missions'] / $operations['total_missions']) * 100
            : 0;

        return [
            'total_missions' => (int)$operations['total_missions'],
            'completed_missions' => (int)$operations['completed_missions'],
            'cancelled_missions' => (int)$operations['cancelled_missions'],
            'completion_rate' => round($completionRate, 1),
            'avg_duration' => round($operations['avg_mission_duration'] ?? 0, 1),
            'total_distance' => (float)($operations['total_distance'] ?? 0)
        ];
    }

    /**
     * Get Maintenance KPIs
     */
    private function getMaintenanceKPIs($companyId, $dateFilter)
    {
        $sql = "SELECT
                    COUNT(*) as total_interventions,
                    COUNT(CASE WHEN type = 'preventive' THEN 1 END) as preventive,
                    COUNT(CASE WHEN type = 'corrective' THEN 1 END) as corrective,
                    AVG(cost) as avg_cost,
                    SUM(downtime_hours) as total_downtime
                FROM maintenance_interventions
                WHERE company_id = :company_id
                AND created_at >= $dateFilter";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);

        $preventiveRate = $maintenance['total_interventions'] > 0
            ? ($maintenance['preventive'] / $maintenance['total_interventions']) * 100
            : 0;

        return [
            'total_interventions' => (int)$maintenance['total_interventions'],
            'preventive' => (int)$maintenance['preventive'],
            'corrective' => (int)$maintenance['corrective'],
            'preventive_rate' => round($preventiveRate, 1),
            'avg_cost' => round($maintenance['avg_cost'] ?? 0, 2),
            'total_downtime' => (int)($maintenance['total_downtime'] ?? 0)
        ];
    }

    /**
     * Get Drivers KPIs
     */
    private function getDriversKPIs($companyId, $dateFilter)
    {
        $sql = "SELECT
                    COUNT(DISTINCT d.id) as total_drivers,
                    COUNT(DISTINCT d.id) FILTER (WHERE d.status = 'active') as active_drivers,
                    AVG(fc.consumption) as avg_fuel_consumption,
                    COUNT(i.id) as total_infractions
                FROM drivers d
                LEFT JOIN fuel_consumption fc ON d.id = fc.driver_id AND fc.created_at >= $dateFilter
                LEFT JOIN infractions i ON d.id = i.driver_id AND i.created_at >= $dateFilter
                WHERE d.company_id = :company_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $drivers = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_drivers' => (int)$drivers['total_drivers'],
            'active_drivers' => (int)$drivers['active_drivers'],
            'avg_fuel_consumption' => round($drivers['avg_fuel_consumption'] ?? 0, 2),
            'total_infractions' => (int)$drivers['total_infractions']
        ];
    }

    /**
     * Get Alerts KPIs
     */
    private function getAlertsKPIs($companyId)
    {
        $sql = "SELECT
                    COUNT(*) as total_alerts,
                    COUNT(CASE WHEN priority = 'critical' THEN 1 END) as critical_alerts,
                    COUNT(CASE WHEN status = 'unread' THEN 1 END) as unread_alerts
                FROM alerts
                WHERE company_id = :company_id
                AND status != 'archived'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();
        $alerts = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_alerts' => (int)$alerts['total_alerts'],
            'critical_alerts' => (int)$alerts['critical_alerts'],
            'unread_alerts' => (int)$alerts['unread_alerts']
        ];
    }

    /**
     * Get trend data for charts
     */
    public function getTrendData($companyId, $metric, $period = 'month')
    {
        $dateFormat = $this->getDateFormat($period);
        $dateFilter = $this->getDateFilter($period);

        switch ($metric) {
            case 'costs':
                return $this->getCostsTrend($companyId, $dateFormat, $dateFilter);
            case 'missions':
                return $this->getMissionsTrend($companyId, $dateFormat, $dateFilter);
            case 'fuel':
                return $this->getFuelTrend($companyId, $dateFormat, $dateFilter);
            case 'maintenance':
                return $this->getMaintenanceTrend($companyId, $dateFormat, $dateFilter);
            default:
                return [];
        }
    }

    /**
     * Get costs trend
     */
    private function getCostsTrend($companyId, $dateFormat, $dateFilter)
    {
        $sql = "SELECT
                    DATE_FORMAT(created_at, '$dateFormat') as period,
                    SUM(CASE WHEN type = 'fuel' THEN amount ELSE 0 END) as fuel,
                    SUM(CASE WHEN type = 'maintenance' THEN amount ELSE 0 END) as maintenance,
                    SUM(CASE WHEN type = 'insurance' THEN amount ELSE 0 END) as insurance,
                    SUM(amount) as total
                FROM expenses
                WHERE company_id = :company_id
                AND created_at >= $dateFilter
                GROUP BY period
                ORDER BY period";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get missions trend
     */
    private function getMissionsTrend($companyId, $dateFormat, $dateFilter)
    {
        $sql = "SELECT
                    DATE_FORMAT(created_at, '$dateFormat') as period,
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                    SUM(distance) as total_distance
                FROM missions
                WHERE company_id = :company_id
                AND created_at >= $dateFilter
                GROUP BY period
                ORDER BY period";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get fuel consumption trend
     */
    private function getFuelTrend($companyId, $dateFormat, $dateFilter)
    {
        $sql = "SELECT
                    DATE_FORMAT(created_at, '$dateFormat') as period,
                    SUM(quantity) as total_quantity,
                    SUM(cost) as total_cost,
                    AVG(price_per_liter) as avg_price
                FROM fuel_consumption
                WHERE company_id = :company_id
                AND created_at >= $dateFilter
                GROUP BY period
                ORDER BY period";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get maintenance trend
     */
    private function getMaintenanceTrend($companyId, $dateFormat, $dateFilter)
    {
        $sql = "SELECT
                    DATE_FORMAT(created_at, '$dateFormat') as period,
                    COUNT(*) as total,
                    COUNT(CASE WHEN type = 'preventive' THEN 1 END) as preventive,
                    COUNT(CASE WHEN type = 'corrective' THEN 1 END) as corrective,
                    SUM(cost) as total_cost
                FROM maintenance_interventions
                WHERE company_id = :company_id
                AND created_at >= $dateFilter
                GROUP BY period
                ORDER BY period";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get vehicle performance comparison
     */
    public function getVehiclePerformance($companyId, $limit = 10)
    {
        $sql = "SELECT
                    v.id,
                    v.registration_number,
                    v.make,
                    v.model,
                    COUNT(m.id) as total_missions,
                    SUM(m.distance) as total_distance,
                    AVG(fc.consumption) as avg_fuel_consumption,
                    SUM(mi.cost) as total_maintenance_cost,
                    (SUM(mi.cost) + SUM(fc.cost)) / NULLIF(SUM(m.distance), 0) as cost_per_km
                FROM vehicles v
                LEFT JOIN missions m ON v.id = m.vehicle_id
                LEFT JOIN fuel_consumption fc ON v.id = fc.vehicle_id
                LEFT JOIN maintenance_interventions mi ON v.id = mi.vehicle_id
                WHERE v.company_id = :company_id
                GROUP BY v.id
                ORDER BY total_missions DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get driver performance comparison
     */
    public function getDriverPerformance($companyId, $limit = 10)
    {
        $sql = "SELECT
                    d.id,
                    d.first_name,
                    d.last_name,
                    COUNT(m.id) as total_missions,
                    SUM(m.distance) as total_distance,
                    AVG(fc.consumption) as avg_fuel_consumption,
                    COUNT(i.id) as total_infractions,
                    AVG(CASE
                        WHEN m.status = 'completed' AND m.end_time IS NOT NULL
                        THEN TIMESTAMPDIFF(MINUTE, m.start_time, m.end_time)
                    END) as avg_mission_duration
                FROM drivers d
                LEFT JOIN missions m ON d.id = m.driver_id
                LEFT JOIN fuel_consumption fc ON d.id = fc.driver_id
                LEFT JOIN infractions i ON d.id = i.driver_id
                WHERE d.company_id = :company_id
                GROUP BY d.id
                ORDER BY total_missions DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Helper: Get date filter SQL
     */
    private function getDateFilter($period)
    {
        switch ($period) {
            case 'day':
                return "DATE(NOW())";
            case 'week':
                return "DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'month':
                return "DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case 'quarter':
                return "DATE_SUB(NOW(), INTERVAL 90 DAY)";
            case 'year':
                return "DATE_SUB(NOW(), INTERVAL 365 DAY)";
            default:
                return "DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }

    /**
     * Helper: Get date format for grouping
     */
    private function getDateFormat($period)
    {
        switch ($period) {
            case 'day':
                return '%Y-%m-%d %H:00';
            case 'week':
            case 'month':
                return '%Y-%m-%d';
            case 'quarter':
            case 'year':
                return '%Y-%m';
            default:
                return '%Y-%m-%d';
        }
    }
}
