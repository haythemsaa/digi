<?php

/**
 * Alert Model
 * Handles intelligent alert system and notifications
 *
 * @author DigiParc Team
 * @version 1.0
 */
class Alert
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all alerts for a company
     *
     * @param int $companyId
     * @param array $filters
     * @return array
     */
    public function getAll($companyId, $filters = [])
    {
        $sql = "SELECT * FROM alerts WHERE company_id = :company_id";

        $params = [':company_id' => $companyId];

        // Apply filters
        if (isset($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }

        if (isset($filters['priority'])) {
            $sql .= " AND priority = :priority";
            $params[':priority'] = $filters['priority'];
        }

        if (isset($filters['type'])) {
            $sql .= " AND type = :type";
            $params[':type'] = $filters['type'];
        }

        $sql .= " ORDER BY
                  FIELD(priority, 'critical', 'high', 'medium', 'low'),
                  created_at DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get alert by ID
     */
    public function getById($id, $companyId)
    {
        $sql = "SELECT * FROM alerts WHERE id = :id AND company_id = :company_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new alert
     */
    public function create($data)
    {
        $sql = "INSERT INTO alerts (
                    company_id, type, priority, title, message,
                    related_type, related_id, action_url, metadata, expires_at
                ) VALUES (
                    :company_id, :type, :priority, :title, :message,
                    :related_type, :related_id, :action_url, :metadata, :expires_at
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $data['company_id']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':related_type', $data['related_type']);
        $stmt->bindParam(':related_id', $data['related_id']);
        $stmt->bindParam(':action_url', $data['action_url']);

        $metadata = isset($data['metadata']) ? json_encode($data['metadata']) : null;
        $stmt->bindParam(':metadata', $metadata);
        $stmt->bindParam(':expires_at', $data['expires_at']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update alert status
     */
    public function updateStatus($id, $status, $companyId)
    {
        $sql = "UPDATE alerts
                SET status = :status
                WHERE id = :id AND company_id = :company_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':company_id', $companyId);

        return $stmt->execute();
    }

    /**
     * Mark alert as read
     */
    public function markAsRead($id, $companyId)
    {
        return $this->updateStatus($id, 'read', $companyId);
    }

    /**
     * Mark alert as acknowledged
     */
    public function acknowledge($id, $companyId)
    {
        return $this->updateStatus($id, 'acknowledged', $companyId);
    }

    /**
     * Archive alert
     */
    public function archive($id, $companyId)
    {
        return $this->updateStatus($id, 'archived', $companyId);
    }

    /**
     * Delete alert
     */
    public function delete($id, $companyId)
    {
        $sql = "DELETE FROM alerts WHERE id = :id AND company_id = :company_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':company_id', $companyId);

        return $stmt->execute();
    }

    /**
     * Get alert statistics
     */
    public function getStatistics($companyId)
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'unread' THEN 1 END) as unread,
                    COUNT(CASE WHEN priority = 'critical' THEN 1 END) as critical,
                    COUNT(CASE WHEN priority = 'high' THEN 1 END) as high,
                    COUNT(CASE WHEN priority = 'medium' THEN 1 END) as medium,
                    COUNT(CASE WHEN priority = 'low' THEN 1 END) as low
                FROM alerts
                WHERE company_id = :company_id
                AND status != 'archived'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check and generate alerts based on rules
     * This should be run by CRON job
     */
    public function checkAlertRules($companyId)
    {
        $generated = [];

        // Check maintenance alerts
        $maintenanceAlerts = $this->checkMaintenanceAlerts($companyId);
        $generated = array_merge($generated, $maintenanceAlerts);

        // Check document expiry
        $expiryAlerts = $this->checkDocumentExpiry($companyId);
        $generated = array_merge($generated, $expiryAlerts);

        // Check fuel anomalies
        $fuelAlerts = $this->checkFuelAnomalies($companyId);
        $generated = array_merge($generated, $fuelAlerts);

        // Check stock levels
        $stockAlerts = $this->checkStockLevels($companyId);
        $generated = array_merge($generated, $stockAlerts);

        return $generated;
    }

    /**
     * Check for maintenance alerts
     */
    private function checkMaintenanceAlerts($companyId)
    {
        $alerts = [];

        // Vehicles needing preventive maintenance (>10000 km or 6 months)
        $sql = "SELECT v.id, v.registration_number, v.make, v.model,
                       COALESCE(MAX(m.mileage), 0) as last_maintenance_km,
                       v.current_mileage,
                       (v.current_mileage - COALESCE(MAX(m.mileage), 0)) as km_since_maintenance
                FROM vehicles v
                LEFT JOIN maintenance_interventions m ON v.id = m.vehicle_id
                WHERE v.company_id = :company_id
                AND v.status = 'active'
                GROUP BY v.id
                HAVING km_since_maintenance >= 10000";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vehicles as $vehicle) {
            // Check if alert already exists
            if (!$this->alertExists($companyId, 'maintenance', 'vehicle', $vehicle['id'])) {
                $alertId = $this->create([
                    'company_id' => $companyId,
                    'type' => 'maintenance',
                    'priority' => 'medium',
                    'title' => 'Maintenance Préventive Requise',
                    'message' => sprintf(
                        'Le véhicule %s (%s %s) a parcouru %s km depuis sa dernière maintenance.',
                        $vehicle['registration_number'],
                        $vehicle['make'],
                        $vehicle['model'],
                        number_format($vehicle['km_since_maintenance'], 0)
                    ),
                    'related_type' => 'vehicle',
                    'related_id' => $vehicle['id'],
                    'action_url' => '/vehicles/view/' . $vehicle['id'],
                    'metadata' => json_encode([
                        'km_since_maintenance' => $vehicle['km_since_maintenance'],
                        'last_maintenance_km' => $vehicle['last_maintenance_km']
                    ]),
                    'expires_at' => null
                ]);

                if ($alertId) {
                    $alerts[] = $alertId;
                }
            }
        }

        return $alerts;
    }

    /**
     * Check for document expiry alerts
     */
    private function checkDocumentExpiry($companyId)
    {
        $alerts = [];

        // Check insurance expiry (30 days warning)
        $sql = "SELECT id, registration_number, make, model, insurance_expiry_date
                FROM vehicles
                WHERE company_id = :company_id
                AND status = 'active'
                AND insurance_expiry_date IS NOT NULL
                AND insurance_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND insurance_expiry_date >= CURDATE()";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vehicles as $vehicle) {
            $daysUntilExpiry = (strtotime($vehicle['insurance_expiry_date']) - time()) / (60 * 60 * 24);

            $priority = 'medium';
            if ($daysUntilExpiry <= 7) {
                $priority = 'critical';
            } elseif ($daysUntilExpiry <= 15) {
                $priority = 'high';
            }

            if (!$this->alertExists($companyId, 'document_expiry', 'vehicle', $vehicle['id'])) {
                $alertId = $this->create([
                    'company_id' => $companyId,
                    'type' => 'document_expiry',
                    'priority' => $priority,
                    'title' => 'Expiration Assurance Imminente',
                    'message' => sprintf(
                        'L\'assurance du véhicule %s expire dans %d jours (%s).',
                        $vehicle['registration_number'],
                        round($daysUntilExpiry),
                        date('d/m/Y', strtotime($vehicle['insurance_expiry_date']))
                    ),
                    'related_type' => 'vehicle',
                    'related_id' => $vehicle['id'],
                    'action_url' => '/vehicles/view/' . $vehicle['id'],
                    'metadata' => json_encode([
                        'expiry_date' => $vehicle['insurance_expiry_date'],
                        'days_until_expiry' => round($daysUntilExpiry)
                    ]),
                    'expires_at' => $vehicle['insurance_expiry_date']
                ]);

                if ($alertId) {
                    $alerts[] = $alertId;
                }
            }
        }

        return $alerts;
    }

    /**
     * Check for fuel consumption anomalies
     */
    private function checkFuelAnomalies($companyId)
    {
        $alerts = [];

        // Get vehicles with abnormal fuel consumption (>15% above average)
        $sql = "SELECT
                    v.id,
                    v.registration_number,
                    AVG(fc.consumption) as avg_consumption,
                    (SELECT AVG(consumption)
                     FROM fuel_consumption
                     WHERE vehicle_id = v.id
                     AND created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)) as avg_60days,
                    (SELECT AVG(consumption)
                     FROM fuel_consumption
                     WHERE vehicle_id = v.id
                     AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as avg_7days
                FROM vehicles v
                JOIN fuel_consumption fc ON v.id = fc.vehicle_id
                WHERE v.company_id = :company_id
                AND v.status = 'active'
                GROUP BY v.id
                HAVING avg_7days > (avg_60days * 1.15)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vehicles as $vehicle) {
            $increase = (($vehicle['avg_7days'] - $vehicle['avg_60days']) / $vehicle['avg_60days']) * 100;

            if (!$this->alertExists($companyId, 'fuel_anomaly', 'vehicle', $vehicle['id'])) {
                $alertId = $this->create([
                    'company_id' => $companyId,
                    'type' => 'fuel_anomaly',
                    'priority' => $increase > 30 ? 'high' : 'medium',
                    'title' => 'Consommation Carburant Anormale',
                    'message' => sprintf(
                        'Le véhicule %s a une consommation %.1f%% supérieure à sa moyenne habituelle.',
                        $vehicle['registration_number'],
                        $increase
                    ),
                    'related_type' => 'vehicle',
                    'related_id' => $vehicle['id'],
                    'action_url' => '/vehicles/view/' . $vehicle['id'],
                    'metadata' => json_encode([
                        'avg_normal' => $vehicle['avg_60days'],
                        'avg_current' => $vehicle['avg_7days'],
                        'increase_percent' => round($increase, 1)
                    ]),
                    'expires_at' => null
                ]);

                if ($alertId) {
                    $alerts[] = $alertId;
                }
            }
        }

        return $alerts;
    }

    /**
     * Check for low stock alerts
     */
    private function checkStockLevels($companyId)
    {
        $alerts = [];

        $sql = "SELECT id, name, current_stock, minimum_stock
                FROM stock_items
                WHERE company_id = :company_id
                AND current_stock <= minimum_stock
                AND status = 'active'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            if (!$this->alertExists($companyId, 'stock_low', 'stock_item', $item['id'])) {
                $alertId = $this->create([
                    'company_id' => $companyId,
                    'type' => 'stock_low',
                    'priority' => 'medium',
                    'title' => 'Stock Minimum Atteint',
                    'message' => sprintf(
                        'Le produit "%s" a atteint le stock minimum (%d / %d unités).',
                        $item['name'],
                        $item['current_stock'],
                        $item['minimum_stock']
                    ),
                    'related_type' => 'stock_item',
                    'related_id' => $item['id'],
                    'action_url' => '/stock/view/' . $item['id'],
                    'metadata' => json_encode([
                        'current_stock' => $item['current_stock'],
                        'minimum_stock' => $item['minimum_stock']
                    ]),
                    'expires_at' => null
                ]);

                if ($alertId) {
                    $alerts[] = $alertId;
                }
            }
        }

        return $alerts;
    }

    /**
     * Check if alert already exists
     */
    private function alertExists($companyId, $type, $relatedType, $relatedId)
    {
        $sql = "SELECT COUNT(*) as count FROM alerts
                WHERE company_id = :company_id
                AND type = :type
                AND related_type = :related_type
                AND related_id = :related_id
                AND status NOT IN ('archived')
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':company_id', $companyId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':related_type', $relatedType);
        $stmt->bindParam(':related_id', $relatedId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Archive expired alerts
     */
    public function archiveExpiredAlerts()
    {
        $sql = "UPDATE alerts
                SET status = 'archived'
                WHERE expires_at IS NOT NULL
                AND expires_at < NOW()
                AND status != 'archived'";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
