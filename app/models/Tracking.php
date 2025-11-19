<?php
/**
 * GPS Tracking Model - Multi-tenant enabled
 */

class Tracking {
    private $db;
    private $companyId;

    public function __construct() {
        $this->db = new Database();
        $this->companyId = getCurrentCompanyId();

        if (!$this->companyId && !isSuperAdmin()) {
            throw new Exception('Company context required');
        }
    }

    private function getCompanyFilter($tableAlias = 'v') {
        if (isSuperAdmin()) {
            return '1=1';
        }
        return "{$tableAlias}.company_id = :company_id";
    }

    private function bindCompanyId() {
        if (!isSuperAdmin()) {
            $this->db->bind(':company_id', $this->companyId);
        }
    }

    /**
     * Get latest position for all vehicles
     */
    public function getLatestPositions() {
        $filter = $this->getCompanyFilter('v');

        $this->db->query("SELECT
            v.id, v.registration_number, v.brand, v.model, v.status,
            gp.latitude, gp.longitude, gp.speed, gp.heading, gp.timestamp,
            gp.fuel_level, gp.engine_status, gp.odometer
            FROM vehicles v
            LEFT JOIN (
                SELECT vehicle_id, latitude, longitude, speed, heading, timestamp,
                       fuel_level, engine_status, odometer
                FROM gps_positions gp1
                WHERE timestamp = (
                    SELECT MAX(timestamp) FROM gps_positions gp2
                    WHERE gp2.vehicle_id = gp1.vehicle_id
                )
            ) gp ON v.id = gp.vehicle_id
            WHERE {$filter} AND v.status = 'active'
            ORDER BY v.registration_number");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Add GPS position
     */
    public function addPosition($data) {
        $this->db->query('INSERT INTO gps_positions (
            vehicle_id, latitude, longitude, altitude, speed, heading,
            accuracy, satellites, odometer, fuel_level, engine_status, timestamp
        ) VALUES (
            :vehicle_id, :latitude, :longitude, :altitude, :speed, :heading,
            :accuracy, :satellites, :odometer, :fuel_level, :engine_status, :timestamp
        )');

        $this->db->bind(':vehicle_id', $data['vehicle_id']);
        $this->db->bind(':latitude', $data['latitude']);
        $this->db->bind(':longitude', $data['longitude']);
        $this->db->bind(':altitude', $data['altitude'] ?? null);
        $this->db->bind(':speed', $data['speed'] ?? 0);
        $this->db->bind(':heading', $data['heading'] ?? null);
        $this->db->bind(':accuracy', $data['accuracy'] ?? null);
        $this->db->bind(':satellites', $data['satellites'] ?? null);
        $this->db->bind(':odometer', $data['odometer'] ?? null);
        $this->db->bind(':fuel_level', $data['fuel_level'] ?? null);
        $this->db->bind(':engine_status', $data['engine_status'] ?? false);
        $this->db->bind(':timestamp', $data['timestamp'] ?? date('Y-m-d H:i:s'));

        return $this->db->execute();
    }

    /**
     * Get vehicle position history
     */
    public function getVehicleHistory($vehicleId, $startDate, $endDate) {
        // Verify vehicle belongs to company
        $filter = $this->getCompanyFilter('v');

        $this->db->query("SELECT gp.* FROM gps_positions gp
                      INNER JOIN vehicles v ON gp.vehicle_id = v.id
                      WHERE gp.vehicle_id = :vehicle_id
                      AND {$filter}
                      AND gp.timestamp BETWEEN :start_date AND :end_date
                      ORDER BY gp.timestamp ASC");

        $this->db->bind(':vehicle_id', $vehicleId);
        $this->bindCompanyId();
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);

        return $this->db->fetchAll();
    }

    /**
     * Get all geofences
     */
    public function getAllGeofences() {
        $filter = $this->getCompanyFilter('g');

        $this->db->query("SELECT g.*, u.first_name, u.last_name
                      FROM geofences g
                      LEFT JOIN users u ON g.created_by = u.id
                      WHERE {$filter} AND g.active = 1
                      ORDER BY g.name");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Add geofence
     */
    public function addGeofence($data) {
        $this->db->query('INSERT INTO geofences (
            company_id, name, type, coordinates, radius, color, description, active, created_by
        ) VALUES (
            :company_id, :name, :type, :coordinates, :radius, :color, :description, :active, :created_by
        )');

        $this->db->bind(':company_id', $this->companyId);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':coordinates', $data['coordinates']);
        $this->db->bind(':radius', $data['radius'] ?? null);
        $this->db->bind(':color', $data['color'] ?? '#FF0000');
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':active', $data['active'] ?? true);
        $this->db->bind(':created_by', $data['created_by']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Get speed alerts
     */
    public function getSpeedAlerts($limit = 50) {
        $filter = $this->getCompanyFilter('v');

        $this->db->query("SELECT sa.*, v.registration_number, v.brand, v.model,
                      d.first_name, d.last_name
                      FROM speed_alerts sa
                      LEFT JOIN vehicles v ON sa.vehicle_id = v.id
                      LEFT JOIN users d ON sa.driver_id = d.id
                      WHERE {$filter}
                      ORDER BY sa.timestamp DESC
                      LIMIT :limit");

        $this->bindCompanyId();
        $this->db->bind(':limit', $limit);
        return $this->db->fetchAll();
    }

    /**
     * Add speed alert
     */
    public function addSpeedAlert($data) {
        $this->db->query('INSERT INTO speed_alerts (
            vehicle_id, driver_id, speed, speed_limit, latitude, longitude, timestamp
        ) VALUES (
            :vehicle_id, :driver_id, :speed, :speed_limit, :latitude, :longitude, :timestamp
        )');

        $this->db->bind(':vehicle_id', $data['vehicle_id']);
        $this->db->bind(':driver_id', $data['driver_id'] ?? null);
        $this->db->bind(':speed', $data['speed']);
        $this->db->bind(':speed_limit', $data['speed_limit']);
        $this->db->bind(':latitude', $data['latitude']);
        $this->db->bind(':longitude', $data['longitude']);
        $this->db->bind(':timestamp', $data['timestamp'] ?? date('Y-m-d H:i:s'));

        return $this->db->execute();
    }

    /**
     * Get active trips
     */
    public function getActiveTrips() {
        $filter = $this->getCompanyFilter('v');

        $this->db->query("SELECT t.*, v.registration_number, v.brand, v.model,
                      d.first_name, d.last_name
                      FROM trips t
                      LEFT JOIN vehicles v ON t.vehicle_id = v.id
                      LEFT JOIN users d ON t.driver_id = d.id
                      WHERE {$filter} AND t.status = 'ongoing'
                      ORDER BY t.start_time DESC");

        $this->bindCompanyId();
        return $this->db->fetchAll();
    }

    /**
     * Start trip
     */
    public function startTrip($data) {
        $this->db->query('INSERT INTO trips (
            vehicle_id, driver_id, start_time, start_location,
            start_latitude, start_longitude, status
        ) VALUES (
            :vehicle_id, :driver_id, :start_time, :start_location,
            :start_latitude, :start_longitude, "ongoing"
        )');

        $this->db->bind(':vehicle_id', $data['vehicle_id']);
        $this->db->bind(':driver_id', $data['driver_id'] ?? null);
        $this->db->bind(':start_time', $data['start_time'] ?? date('Y-m-d H:i:s'));
        $this->db->bind(':start_location', $data['start_location'] ?? null);
        $this->db->bind(':start_latitude', $data['start_latitude']);
        $this->db->bind(':start_longitude', $data['start_longitude']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * End trip
     */
    public function endTrip($tripId, $data) {
        // Verify trip belongs to company vehicle
        $filter = $this->getCompanyFilter('v');

        $this->db->query("UPDATE trips t
            INNER JOIN vehicles v ON t.vehicle_id = v.id
            SET
            t.end_time = :end_time,
            t.end_location = :end_location,
            t.end_latitude = :end_latitude,
            t.end_longitude = :end_longitude,
            t.distance = :distance,
            t.duration = :duration,
            t.max_speed = :max_speed,
            t.avg_speed = :avg_speed,
            t.fuel_consumed = :fuel_consumed,
            t.status = 'completed'
            WHERE t.id = :id AND {$filter}");

        $this->db->bind(':id', $tripId);
        $this->bindCompanyId();
        $this->db->bind(':end_time', $data['end_time'] ?? date('Y-m-d H:i:s'));
        $this->db->bind(':end_location', $data['end_location'] ?? null);
        $this->db->bind(':end_latitude', $data['end_latitude']);
        $this->db->bind(':end_longitude', $data['end_longitude']);
        $this->db->bind(':distance', $data['distance'] ?? 0);
        $this->db->bind(':duration', $data['duration'] ?? 0);
        $this->db->bind(':max_speed', $data['max_speed'] ?? 0);
        $this->db->bind(':avg_speed', $data['avg_speed'] ?? 0);
        $this->db->bind(':fuel_consumed', $data['fuel_consumed'] ?? 0);

        return $this->db->execute();
    }

    /**
     * Get trip details
     */
    public function getTripById($id) {
        $filter = $this->getCompanyFilter('v');

        $this->db->query("SELECT t.*, v.registration_number, v.brand, v.model,
                      d.first_name, d.last_name
                      FROM trips t
                      LEFT JOIN vehicles v ON t.vehicle_id = v.id
                      LEFT JOIN users d ON t.driver_id = d.id
                      WHERE t.id = :id AND {$filter}");

        $this->db->bind(':id', $id);
        $this->bindCompanyId();
        return $this->db->fetch();
    }

    /**
     * Get geofence alerts
     */
    public function getGeofenceAlerts($limit = 50) {
        $filter = $this->getCompanyFilter('v');

        $this->db->query("SELECT ga.*, v.registration_number, gf.name as geofence_name
                      FROM geofence_alerts ga
                      LEFT JOIN vehicles v ON ga.vehicle_id = v.id
                      LEFT JOIN geofences gf ON ga.geofence_id = gf.id
                      WHERE {$filter}
                      ORDER BY ga.timestamp DESC
                      LIMIT :limit");

        $this->bindCompanyId();
        $this->db->bind(':limit', $limit);
        return $this->db->fetchAll();
    }

    /**
     * Get trip history
     */
    public function getTripHistory($vehicleId = null, $limit = 50) {
        $filter = $this->getCompanyFilter('v');

        $sql = "SELECT t.*, v.registration_number, d.first_name, d.last_name
                FROM trips t
                LEFT JOIN vehicles v ON t.vehicle_id = v.id
                LEFT JOIN users d ON t.driver_id = d.id
                WHERE {$filter}";

        if ($vehicleId) {
            $sql .= " AND t.vehicle_id = :vehicle_id";
        }

        $sql .= " ORDER BY t.start_time DESC LIMIT :limit";

        $this->db->query($sql);
        $this->bindCompanyId();

        if ($vehicleId) {
            $this->db->bind(':vehicle_id', $vehicleId);
        }

        $this->db->bind(':limit', $limit);
        return $this->db->fetchAll();
    }
}
