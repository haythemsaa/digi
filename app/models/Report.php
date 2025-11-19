<?php

/**
 * Report Model
 * Handles report generation and management
 *
 * @author Pakiparc Team
 * @version 1.0
 */

class Report
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Generate fleet report
     */
    public function generateFleetReport($companyId, $startDate, $endDate)
    {
        $sql = "SELECT
                    v.id,
                    v.registration,
                    v.make,
                    v.model,
                    v.status,
                    v.mileage,
                    COUNT(DISTINCT m.id) as mission_count,
                    SUM(m.distance) as total_distance,
                    COUNT(DISTINCT ma.id) as maintenance_count,
                    SUM(ma.cost) as maintenance_cost
                FROM vehicles v
                LEFT JOIN missions m ON v.id = m.vehicle_id
                    AND m.start_date BETWEEN :start_date AND :end_date
                LEFT JOIN maintenance ma ON v.id = ma.vehicle_id
                    AND ma.scheduled_date BETWEEN :start_date AND :end_date
                WHERE v.company_id = :company_id
                GROUP BY v.id
                ORDER BY v.registration";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate driver report
     */
    public function generateDriverReport($companyId, $startDate, $endDate)
    {
        $sql = "SELECT
                    d.id,
                    CONCAT(d.first_name, ' ', d.last_name) as driver_name,
                    d.license_number,
                    d.status,
                    COUNT(DISTINCT m.id) as mission_count,
                    SUM(m.distance) as total_distance,
                    AVG(m.distance) as avg_distance,
                    SUM(ct.co2_emissions) as total_emissions
                FROM drivers d
                LEFT JOIN missions m ON d.id = m.driver_id
                    AND m.start_date BETWEEN :start_date AND :end_date
                LEFT JOIN carbon_tracking ct ON d.id = ct.driver_id
                    AND ct.date BETWEEN :start_date AND :end_date
                WHERE d.company_id = :company_id
                GROUP BY d.id
                ORDER BY driver_name";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport($companyId, $startDate, $endDate)
    {
        // Revenue
        $revenueSql = "SELECT
                        SUM(amount) as total_revenue
                      FROM financial_transactions
                      WHERE company_id = :company_id
                      AND type = 'revenue'
                      AND date BETWEEN :start_date AND :end_date";

        $revenueStmt = $this->db->prepare($revenueSql);
        $revenueStmt->bindParam(':company_id', $companyId);
        $revenueStmt->bindParam(':start_date', $startDate);
        $revenueStmt->bindParam(':end_date', $endDate);
        $revenueStmt->execute();
        $revenue = $revenueStmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

        // Expenses
        $expensesSql = "SELECT
                         SUM(amount) as total_expenses
                       FROM financial_transactions
                       WHERE company_id = :company_id
                       AND type = 'expense'
                       AND date BETWEEN :start_date AND :end_date";

        $expensesStmt = $this->db->prepare($expensesSql);
        $expensesStmt->bindParam(':company_id', $companyId);
        $expensesStmt->bindParam(':start_date', $startDate);
        $expensesStmt->bindParam(':end_date', $endDate);
        $expensesStmt->execute();
        $expenses = $expensesStmt->fetch(PDO::FETCH_ASSOC)['total_expenses'] ?? 0;

        // Expenses by category
        $categorySql = "SELECT
                         category,
                         SUM(amount) as total
                       FROM financial_transactions
                       WHERE company_id = :company_id
                       AND type = 'expense'
                       AND date BETWEEN :start_date AND :end_date
                       GROUP BY category
                       ORDER BY total DESC";

        $categoryStmt = $this->db->prepare($categorySql);
        $categoryStmt->bindParam(':company_id', $companyId);
        $categoryStmt->bindParam(':start_date', $startDate);
        $categoryStmt->bindParam(':end_date', $endDate);
        $categoryStmt->execute();
        $byCategory = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $revenue - $expenses,
            'profit_margin' => $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0,
            'by_category' => $byCategory
        ];
    }

    /**
     * Generate maintenance report
     */
    public function generateMaintenanceReport($companyId, $startDate, $endDate)
    {
        $sql = "SELECT
                    m.*,
                    v.registration,
                    v.make,
                    v.model
                FROM maintenance m
                INNER JOIN vehicles v ON m.vehicle_id = v.id
                WHERE m.company_id = :company_id
                AND m.scheduled_date BETWEEN :start_date AND :end_date
                ORDER BY m.scheduled_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate carbon report
     */
    public function generateCarbonReport($companyId, $startDate, $endDate)
    {
        $sql = "SELECT
                    DATE(date) as report_date,
                    SUM(co2_emissions) as daily_emissions,
                    COUNT(DISTINCT vehicle_id) as vehicles_used,
                    COUNT(DISTINCT driver_id) as drivers_active
                FROM carbon_tracking
                WHERE company_id = :company_id
                AND date BETWEEN :start_date AND :end_date
                GROUP BY DATE(date)
                ORDER BY report_date";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Save generated report
     */
    public function saveReport($companyId, $type, $data, $format = 'pdf')
    {
        $sql = "INSERT INTO reports (company_id, type, format, data, created_at)
                VALUES (:company_id, :type, :format, :data, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':format', $format);
        $dataJson = json_encode($data);
        $stmt->bindParam(':data', $dataJson);
        $stmt->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Get saved reports
     */
    public function getReports($companyId, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM reports
                WHERE company_id = :company_id
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
