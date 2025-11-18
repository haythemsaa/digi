<?php
/**
 * GPS Tracking Model
 */

class Tracking extends Database {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get latest position for all vehicles
     */
    public function getLatestPositions() {
        $this->query('SELECT
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
            WHERE v.status = "active"
            ORDER BY v.registration_number');
        return $this->fetchAll();
    }

    /**
     * Add GPS position
     */
    public function addPosition($data) {
        $this->query('INSERT INTO gps_positions (
            vehicle_id, latitude, longitude, altitude, speed, heading,
            accuracy, satellites, odometer, fuel_level, engine_status, timestamp
        ) VALUES (
            :vehicle_id, :latitude, :longitude, :altitude, :speed, :heading,
            :accuracy, :satellites, :odometer, :fuel_level, :engine_status, :timestamp
        )');

        $this->bind(':vehicle_id', $data['vehicle_id']);
        $this->bind(':latitude', $data['latitude']);
        $this->bind(':longitude', $data['longitude']);
        $this->bind(':altitude', $data['altitude'] ?? null);
        $this->bind(':speed', $data['speed'] ?? 0);
        $this->bind(':heading', $data['heading'] ?? null);
        $this->bind(':accuracy', $data['accuracy'] ?? null);
        $this->bind(':satellites', $data['satellites'] ?? null);
        $this->bind(':odometer', $data['odometer'] ?? null);
        $this->bind(':fuel_level', $data['fuel_level'] ?? null);
        $this->bind(':engine_status', $data['engine_status'] ?? false);
        $this->bind(':timestamp', $data['timestamp'] ?? date('Y-m-d H:i:s'));

        return $this->execute();
    }

    /**
     * Get vehicle position history
     */
    public function getVehicleHistory($vehicleId, $startDate, $endDate) {
        $this->query('SELECT * FROM gps_positions
                      WHERE vehicle_id = :vehicle_id
                      AND timestamp BETWEEN :start_date AND :end_date
                      ORDER BY timestamp ASC');
        $this->bind(':vehicle_id', $vehicleId);
        $this->bind(':start_date', $startDate);
        $this->bind(':end_date', $endDate);
        return $this->fetchAll();
    }

    /**
     * Get all geofences
     */
    public function getAllGeofences() {
        $this->query('SELECT g.*, u.first_name, u.last_name
                      FROM geofences g
                      LEFT JOIN users u ON g.created_by = u.id
                      WHERE g.active = 1
                      ORDER BY g.name');
        return $this->fetchAll();
    }

    /**
     * Add geofence
     */
    public function addGeofence($data) {
        $this->query('INSERT INTO geofences (
            name, type, coordinates, radius, color, description, active, created_by
        ) VALUES (
            :name, :type, :coordinates, :radius, :color, :description, :active, :created_by
        )');

        $this->bind(':name', $data['name']);
        $this->bind(':type', $data['type']);
        $this->bind(':coordinates', $data['coordinates']);
        $this->bind(':radius', $data['radius'] ?? null);
        $this->bind(':color', $data['color'] ?? '#FF0000');
        $this->bind(':description', $data['description'] ?? null);
        $this->bind(':active', $data['active'] ?? true);
        $this->bind(':created_by', $data['created_by']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    /**
     * Get speed alerts
     */
    public function getSpeedAlerts($limit = 50) {
        $this->query('SELECT sa.*, v.registration_number, v.brand, v.model,
                      d.first_name, d.last_name
                      FROM speed_alerts sa
                      LEFT JOIN vehicles v ON sa.vehicle_id = v.id
                      LEFT JOIN users d ON sa.driver_id = d.id
                      ORDER BY sa.timestamp DESC
                      LIMIT :limit');
        $this->bind(':limit', $limit);
        return $this->fetchAll();
    }

    /**
     * Add speed alert
     */
    public function addSpeedAlert($data) {
        $this->query('INSERT INTO speed_alerts (
            vehicle_id, driver_id, speed, speed_limit, latitude, longitude, timestamp
        ) VALUES (
            :vehicle_id, :driver_id, :speed, :speed_limit, :latitude, :longitude, :timestamp
        )');

        $this->bind(':vehicle_id', $data['vehicle_id']);
        $this->bind(':driver_id', $data['driver_id'] ?? null);
        $this->bind(':speed', $data['speed']);
        $this->bind(':speed_limit', $data['speed_limit']);
        $this->bind(':latitude', $data['latitude']);
        $this->bind(':longitude', $data['longitude']);
        $this->bind(':timestamp', $data['timestamp'] ?? date('Y-m-d H:i:s'));

        return $this->execute();
    }

    /**
     * Get active trips
     */
    public function getActiveTrips() {
        $this->query('SELECT t.*, v.registration_number, v.brand, v.model,
                      d.first_name, d.last_name
                      FROM trips t
                      LEFT JOIN vehicles v ON t.vehicle_id = v.id
                      LEFT JOIN users d ON t.driver_id = d.id
                      WHERE t.status = "ongoing"
                      ORDER BY t.start_time DESC');
        return $this->fetchAll();
    }

    /**
     * Start trip
     */
    public function startTrip($data) {
        $this->query('INSERT INTO trips (
            vehicle_id, driver_id, start_time, start_location,
            start_latitude, start_longitude, status
        ) VALUES (
            :vehicle_id, :driver_id, :start_time, :start_location,
            :start_latitude, :start_longitude, "ongoing"
        )');

        $this->bind(':vehicle_id', $data['vehicle_id']);
        $this->bind(':driver_id', $data['driver_id'] ?? null);
        $this->bind(':start_time', $data['start_time'] ?? date('Y-m-d H:i:s'));
        $this->bind(':start_location', $data['start_location'] ?? null);
        $this->bind(':start_latitude', $data['start_latitude']);
        $this->bind(':start_longitude', $data['start_longitude']);

        if ($this->execute()) {
            return $this->lastInsertId();
        }
        return false;
    }

    /**
     * End trip
     */
    public function endTrip($tripId, $data) {
        $this->query('UPDATE trips SET
            end_time = :end_time,
            end_location = :end_location,
            end_latitude = :end_latitude,
            end_longitude = :end_longitude,
            distance = :distance,
            duration = :duration,
            max_speed = :max_speed,
            avg_speed = :avg_speed,
            fuel_consumed = :fuel_consumed,
            status = "completed"
            WHERE id = :id');

        $this->bind(':id', $tripId);
        $this->bind(':end_time', $data['end_time'] ?? date('Y-m-d H:i:s'));
        $this->bind(':end_location', $data['end_location'] ?? null);
        $this->bind(':end_latitude', $data['end_latitude']);
        $this->bind(':end_longitude', $data['end_longitude']);
        $this->bind(':distance', $data['distance'] ?? 0);
        $this->bind(':duration', $data['duration'] ?? 0);
        $this->bind(':max_speed', $data['max_speed'] ?? 0);
        $this->bind(':avg_speed', $data['avg_speed'] ?? 0);
        $this->bind(':fuel_consumed', $data['fuel_consumed'] ?? 0);

        return $this->execute();
    }

    /**
     * Get trip details
     */
    public function getTripById($id) {
        $this->query('SELECT t.*, v.registration_number, v.brand, v.model,
                      d.first_name, d.last_name
                      FROM trips t
                      LEFT JOIN vehicles v ON t.vehicle_id = v.id
                      LEFT JOIN users d ON t.driver_id = d.id
                      WHERE t.id = :id');
        $this->bind(':id', $id);
        return $this->fetch();
    }

    /**
     * Get geofence alerts
     */
    public function getGeofenceAlerts($limit = 50) {
        $this->query('SELECT ga.*, v.registration_number, gf.name as geofence_name
                      FROM geofence_alerts ga
                      LEFT JOIN vehicles v ON ga.vehicle_id = v.id
                      LEFT JOIN geofences gf ON ga.geofence_id = gf.id
                      ORDER BY ga.timestamp DESC
                      LIMIT :limit');
        $this->bind(':limit', $limit);
        return $this->fetchAll();
    }
}
