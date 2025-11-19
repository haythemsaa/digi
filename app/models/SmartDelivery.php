<?php
/**
 * Smart Delivery Model
 * AI-powered delivery optimization with 3D bin packing and route optimization
 */

class SmartDelivery extends Model {

    // ==================== PACKAGES ====================

    /**
     * Get all packages
     */
    public function getAllPackages($filters = []) {
        $sql = "SELECT p.*, c.name as client_name
                FROM packages p
                LEFT JOIN clients c ON p.client_id = c.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND p.priority = ?";
            $params[] = $filters['priority'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get package by ID
     */
    public function getPackageById($id) {
        $this->db->query("SELECT p.*, c.name as client_name, c.phone as client_phone
                         FROM packages p
                         LEFT JOIN clients c ON p.client_id = c.id
                         WHERE p.id = ?");
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    /**
     * Create package
     */
    public function createPackage($data) {
        // Generate package number
        $packageNumber = $this->generatePackageNumber();

        // Calculate volume (L x W x H in cm³ to m³)
        $volume = ($data['length'] * $data['width'] * $data['height']) / 1000000;

        $this->db->query("INSERT INTO packages
                         (package_number, client_id, order_number, description,
                          weight, length, width, height, volume,
                          is_fragile, is_stackable, rotation_allowed, priority,
                          delivery_address, delivery_lat, delivery_lng,
                          delivery_contact, delivery_phone, delivery_notes,
                          time_window_start, time_window_end, status)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

        $this->db->bind(1, $packageNumber);
        $this->db->bind(2, $data['client_id'] ?? null);
        $this->db->bind(3, $data['order_number'] ?? null);
        $this->db->bind(4, $data['description']);
        $this->db->bind(5, $data['weight']);
        $this->db->bind(6, $data['length']);
        $this->db->bind(7, $data['width']);
        $this->db->bind(8, $data['height']);
        $this->db->bind(9, $volume);
        $this->db->bind(10, $data['is_fragile'] ?? 0);
        $this->db->bind(11, $data['is_stackable'] ?? 1);
        $this->db->bind(12, $data['rotation_allowed'] ?? 1);
        $this->db->bind(13, $data['priority'] ?? 'normal');
        $this->db->bind(14, $data['delivery_address']);
        $this->db->bind(15, $data['delivery_lat'] ?? null);
        $this->db->bind(16, $data['delivery_lng'] ?? null);
        $this->db->bind(17, $data['delivery_contact'] ?? null);
        $this->db->bind(18, $data['delivery_phone'] ?? null);
        $this->db->bind(19, $data['delivery_notes'] ?? null);
        $this->db->bind(20, $data['time_window_start'] ?? null);
        $this->db->bind(21, $data['time_window_end'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Generate unique package number
     */
    private function generatePackageNumber() {
        $year = date('Y');
        $month = date('m');
        $prefix = 'PKG-' . $year . $month . '-';

        $this->db->query("SELECT package_number FROM packages
                         WHERE package_number LIKE ?
                         ORDER BY package_number DESC LIMIT 1");
        $this->db->bind(1, $prefix . '%');
        $result = $this->db->single();

        if ($result) {
            $lastNumber = intval(substr($result['package_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Get pending packages for route planning
     */
    public function getPendingPackages() {
        $this->db->query("SELECT * FROM packages WHERE status = 'pending' ORDER BY priority DESC, created_at ASC");
        return $this->db->resultSet();
    }

    // ==================== DELIVERY ROUTES ====================

    /**
     * Get all routes
     */
    public function getAllRoutes($filters = []) {
        $sql = "SELECT dr.*,
                       v.registration_number, v.make, v.model,
                       CONCAT(u.first_name, ' ', u.last_name) as driver_name
                FROM delivery_routes dr
                LEFT JOIN vehicles v ON dr.vehicle_id = v.id
                LEFT JOIN users u ON dr.driver_id = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND dr.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date'])) {
            $sql .= " AND dr.route_date = ?";
            $params[] = $filters['date'];
        }

        $sql .= " ORDER BY dr.route_date DESC, dr.created_at DESC";

        $this->db->query($sql);
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $this->db->bind($i + 1, $param);
            }
        }

        return $this->db->resultSet();
    }

    /**
     * Get route by ID with all details
     */
    public function getRouteById($id) {
        $this->db->query("SELECT dr.*,
                                 v.registration_number, v.make, v.model,
                                 CONCAT(u.first_name, ' ', u.last_name) as driver_name,
                                 vdc.cargo_length, vdc.cargo_width, vdc.cargo_height, vdc.max_weight
                          FROM delivery_routes dr
                          LEFT JOIN vehicles v ON dr.vehicle_id = v.id
                          LEFT JOIN users u ON dr.driver_id = u.id
                          LEFT JOIN vehicle_delivery_capacity vdc ON v.id = vdc.vehicle_id
                          WHERE dr.id = ?");
        $this->db->bind(1, $id);
        $route = $this->db->single();

        if ($route) {
            // Get stops
            $route['stops'] = $this->getRouteStops($id);
            // Get loading plan
            $route['loading_plan'] = $this->getLoadingPlan($id);
        }

        return $route;
    }

    /**
     * Create delivery route
     */
    public function createRoute($data) {
        $routeNumber = $this->generateRouteNumber();

        $this->db->query("INSERT INTO delivery_routes
                         (route_number, vehicle_id, driver_id, route_date,
                          start_location, start_lat, start_lng,
                          status, created_by)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', ?)");

        $this->db->bind(1, $routeNumber);
        $this->db->bind(2, $data['vehicle_id']);
        $this->db->bind(3, $data['driver_id'] ?? null);
        $this->db->bind(4, $data['route_date']);
        $this->db->bind(5, $data['start_location'] ?? 'Warehouse');
        $this->db->bind(6, $data['start_lat'] ?? null);
        $this->db->bind(7, $data['start_lng'] ?? null);
        $this->db->bind(8, $_SESSION['user_id'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Generate route number
     */
    private function generateRouteNumber() {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $prefix = 'RT-' . $year . $month . $day . '-';

        $this->db->query("SELECT route_number FROM delivery_routes
                         WHERE route_number LIKE ?
                         ORDER BY route_number DESC LIMIT 1");
        $this->db->bind(1, $prefix . '%');
        $result = $this->db->single();

        if ($result) {
            $lastNumber = intval(substr($result['route_number'], -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }

    /**
     * Get route stops
     */
    public function getRouteStops($routeId) {
        $this->db->query("SELECT ds.*, p.package_number, p.description, p.weight,
                                 p.delivery_contact, p.delivery_phone
                         FROM delivery_stops ds
                         LEFT JOIN packages p ON ds.package_id = p.id
                         WHERE ds.route_id = ?
                         ORDER BY ds.stop_sequence ASC");
        $this->db->bind(1, $routeId);
        return $this->db->resultSet();
    }

    /**
     * Add package to route
     */
    public function addPackageToRoute($routeId, $packageId, $sequence) {
        // Get package details
        $package = $this->getPackageById($packageId);

        $this->db->query("INSERT INTO delivery_stops
                         (route_id, package_id, stop_sequence, address,
                          latitude, longitude, service_time, status)
                         VALUES (?, ?, ?, ?, ?, ?, 10, 'pending')");

        $this->db->bind(1, $routeId);
        $this->db->bind(2, $packageId);
        $this->db->bind(3, $sequence);
        $this->db->bind(4, $package['delivery_address']);
        $this->db->bind(5, $package['delivery_lat']);
        $this->db->bind(6, $package['delivery_lng']);

        if ($this->db->execute()) {
            // Update package status
            $this->db->query("UPDATE packages SET status = 'assigned' WHERE id = ?");
            $this->db->bind(1, $packageId);
            $this->db->execute();

            return true;
        }
        return false;
    }

    // ==================== AI OPTIMIZATION ====================

    /**
     * Optimize route using AI (VRP with Time Windows)
     * This is a simplified version - in production, use external AI service
     */
    public function optimizeRoute($routeId) {
        $startTime = microtime(true);

        $route = $this->getRouteById($routeId);
        if (!$route || empty($route['stops'])) {
            return ['success' => false, 'error' => 'Invalid route or no stops'];
        }

        // Get AI settings
        $settings = $this->getAISettings('route_optimization');
        $params = json_decode($settings['parameters'], true);

        // Simplified genetic algorithm for route optimization
        $optimizedStops = $this->geneticAlgorithmVRP($route['stops'], $params);

        // Calculate distances
        $initialDistance = $this->calculateTotalDistance($route['stops']);
        $optimizedDistance = $this->calculateTotalDistance($optimizedStops);
        $distanceSaved = $initialDistance - $optimizedDistance;
        $improvement = ($distanceSaved / $initialDistance) * 100;

        $computationTime = microtime(true) - $startTime;

        // Update stop sequences
        foreach ($optimizedStops as $index => $stop) {
            $this->db->query("UPDATE delivery_stops SET stop_sequence = ? WHERE id = ?");
            $this->db->bind(1, $index + 1);
            $this->db->bind(2, $stop['id']);
            $this->db->execute();
        }

        // Save optimization record
        $this->db->query("INSERT INTO route_optimizations
                         (route_id, optimization_type, algorithm, initial_distance,
                          optimized_distance, distance_saved, percentage_improvement,
                          computation_time, parameters)
                         VALUES (?, 'vrptw', ?, ?, ?, ?, ?, ?, ?)");

        $this->db->bind(1, $routeId);
        $this->db->bind(2, $params['algorithm']);
        $this->db->bind(3, $initialDistance);
        $this->db->bind(4, $optimizedDistance);
        $this->db->bind(5, $distanceSaved);
        $this->db->bind(6, $improvement);
        $this->db->bind(7, $computationTime);
        $this->db->bind(8, json_encode($params));
        $this->db->execute();

        // Update route metrics
        $this->db->query("UPDATE delivery_routes
                         SET total_distance = ?,
                             route_efficiency = ?,
                             status = 'optimized'
                         WHERE id = ?");
        $this->db->bind(1, $optimizedDistance);
        $this->db->bind(2, $improvement);
        $this->db->bind(3, $routeId);
        $this->db->execute();

        return [
            'success' => true,
            'initial_distance' => $initialDistance,
            'optimized_distance' => $optimizedDistance,
            'distance_saved' => $distanceSaved,
            'improvement_percentage' => round($improvement, 2),
            'computation_time' => round($computationTime, 2)
        ];
    }

    /**
     * Simplified Genetic Algorithm for VRP
     * Note: This is a basic implementation. For production, use specialized libraries
     */
    private function geneticAlgorithmVRP($stops, $params) {
        $populationSize = $params['population_size'] ?? 50;
        $generations = $params['generations'] ?? 100;
        $mutationRate = $params['mutation_rate'] ?? 0.15;

        // Create initial population
        $population = [];
        for ($i = 0; $i < $populationSize; $i++) {
            $individual = $stops;
            shuffle($individual);
            $population[] = $individual;
        }

        // Evolve
        for ($gen = 0; $gen < $generations; $gen++) {
            // Evaluate fitness (shorter distance = better fitness)
            $fitness = [];
            foreach ($population as $individual) {
                $distance = $this->calculateTotalDistance($individual);
                $fitness[] = 1 / ($distance + 1); // Inverse distance as fitness
            }

            // Selection - keep best individuals
            $newPopulation = [];
            for ($i = 0; $i < $populationSize; $i++) {
                $parent1 = $this->rouletteSelection($population, $fitness);
                $parent2 = $this->rouletteSelection($population, $fitness);

                // Crossover (Order Crossover)
                $child = $this->orderCrossover($parent1, $parent2);

                // Mutation
                if (rand() / getrandmax() < $mutationRate) {
                    $child = $this->swapMutation($child);
                }

                $newPopulation[] = $child;
            }

            $population = $newPopulation;
        }

        // Return best solution
        $bestIndex = 0;
        $bestDistance = PHP_FLOAT_MAX;
        foreach ($population as $index => $individual) {
            $distance = $this->calculateTotalDistance($individual);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestIndex = $index;
            }
        }

        return $population[$bestIndex];
    }

    /**
     * Calculate total distance for a route
     */
    private function calculateTotalDistance($stops) {
        $totalDistance = 0;
        for ($i = 0; $i < count($stops) - 1; $i++) {
            $distance = $this->haversineDistance(
                $stops[$i]['latitude'],
                $stops[$i]['longitude'],
                $stops[$i + 1]['latitude'],
                $stops[$i + 1]['longitude']
            );
            $totalDistance += $distance;
        }
        return $totalDistance;
    }

    /**
     * Haversine formula to calculate distance between two GPS coordinates
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Roulette wheel selection
     */
    private function rouletteSelection($population, $fitness) {
        $totalFitness = array_sum($fitness);
        $pick = (rand() / getrandmax()) * $totalFitness;

        $current = 0;
        foreach ($fitness as $index => $f) {
            $current += $f;
            if ($current > $pick) {
                return $population[$index];
            }
        }

        return $population[0];
    }

    /**
     * Order crossover for TSP/VRP
     */
    private function orderCrossover($parent1, $parent2) {
        $size = count($parent1);
        $start = rand(0, $size - 2);
        $end = rand($start + 1, $size - 1);

        $child = array_fill(0, $size, null);

        // Copy substring from parent1
        for ($i = $start; $i <= $end; $i++) {
            $child[$i] = $parent1[$i];
        }

        // Fill remaining from parent2
        $parent2Index = 0;
        for ($i = 0; $i < $size; $i++) {
            if ($child[$i] === null) {
                while (in_array($parent2[$parent2Index], $child, true)) {
                    $parent2Index++;
                }
                $child[$i] = $parent2[$parent2Index];
                $parent2Index++;
            }
        }

        return $child;
    }

    /**
     * Swap mutation
     */
    private function swapMutation($individual) {
        $size = count($individual);
        $index1 = rand(0, $size - 1);
        $index2 = rand(0, $size - 1);

        $temp = $individual[$index1];
        $individual[$index1] = $individual[$index2];
        $individual[$index2] = $temp;

        return $individual;
    }

    // ==================== 3D BIN PACKING ====================

    /**
     * Optimize loading using 3D Bin Packing
     */
    public function optimizeLoading($routeId) {
        $startTime = microtime(true);

        $route = $this->getRouteById($routeId);
        if (!$route) {
            return ['success' => false, 'error' => 'Route not found'];
        }

        // Get packages for this route
        $packages = [];
        foreach ($route['stops'] as $stop) {
            $pkg = $this->getPackageById($stop['package_id']);
            $packages[] = $pkg;
        }

        // Get vehicle capacity
        $capacity = [
            'length' => $route['cargo_length'],
            'width' => $route['cargo_width'],
            'height' => $route['cargo_height'],
            'max_weight' => $route['max_weight']
        ];

        // Run 3D bin packing algorithm
        $result = $this->binPacking3D($packages, $capacity);

        $computationTime = microtime(true) - $startTime;

        // Create loading plan
        $planNumber = 'LP-' . date('Ymd') . '-' . str_pad($routeId, 4, '0', STR_PAD_LEFT);

        $this->db->query("INSERT INTO loading_plans
                         (route_id, plan_number, algorithm_used, computation_time,
                          space_utilization, total_packages_fitted, total_packages_planned,
                          is_valid)
                         VALUES (?, ?, 'bin_packing_3d', ?, ?, ?, ?, ?)");

        $this->db->bind(1, $routeId);
        $this->db->bind(2, $planNumber);
        $this->db->bind(3, $computationTime);
        $this->db->bind(4, $result['space_utilization']);
        $this->db->bind(5, $result['fitted_count']);
        $this->db->bind(6, count($packages));
        $this->db->bind(7, $result['fitted_count'] == count($packages) ? 1 : 0);
        $this->db->execute();

        $loadingPlanId = $this->db->lastInsertId();

        // Save loading instructions
        foreach ($result['placements'] as $index => $placement) {
            $this->db->query("INSERT INTO loading_instructions
                             (loading_plan_id, package_id, load_sequence,
                              position_x, position_y, position_z, rotation,
                              instruction_text, warnings)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $this->db->bind(1, $loadingPlanId);
            $this->db->bind(2, $placement['package_id']);
            $this->db->bind(3, $index + 1);
            $this->db->bind(4, $placement['x']);
            $this->db->bind(5, $placement['y']);
            $this->db->bind(6, $placement['z']);
            $this->db->bind(7, $placement['rotation']);
            $this->db->bind(8, $placement['instruction']);
            $this->db->bind(9, $placement['warnings']);
            $this->db->execute();
        }

        // Update route loading efficiency
        $this->db->query("UPDATE delivery_routes
                         SET loading_efficiency = ?
                         WHERE id = ?");
        $this->db->bind(1, $result['space_utilization']);
        $this->db->bind(2, $routeId);
        $this->db->execute();

        return array_merge(['success' => true], $result);
    }

    /**
     * Simplified 3D Bin Packing Algorithm
     * Note: This is a basic implementation using First Fit Decreasing
     */
    private function binPacking3D($packages, $capacity) {
        // Sort packages by volume (largest first)
        usort($packages, function($a, $b) {
            return floatval($b['volume']) - floatval($a['volume']);
        });

        $placements = [];
        $occupiedSpaces = [];
        $fittedCount = 0;
        $totalVolume = ($capacity['length'] * $capacity['width'] * $capacity['height']) / 1000000; // m³
        $usedVolume = 0;

        foreach ($packages as $pkg) {
            $placed = false;

            // Try to find a position for this package
            for ($x = 0; $x <= $capacity['length'] - $pkg['length'] && !$placed; $x += 10) {
                for ($y = 0; $y <= $capacity['width'] - $pkg['width'] && !$placed; $y += 10) {
                    for ($z = 0; $z <= $capacity['height'] - $pkg['height'] && !$placed; $z += 10) {
                        if ($this->canPlacePackage($pkg, $x, $y, $z, $occupiedSpaces, $capacity)) {
                            $placements[] = [
                                'package_id' => $pkg['id'],
                                'x' => $x,
                                'y' => $y,
                                'z' => $z,
                                'rotation' => 'normal',
                                'instruction' => "Place package {$pkg['package_number']} at position (X:{$x}cm, Y:{$y}cm, Z:{$z}cm)",
                                'warnings' => ($pkg['is_fragile'] ? 'FRAGILE - Handle with care. ' : '') .
                                             ($pkg['weight'] > 20 ? 'HEAVY - ' . $pkg['weight'] . 'kg. ' : '')
                            ];

                            $occupiedSpaces[] = [
                                'x1' => $x,
                                'y1' => $y,
                                'z1' => $z,
                                'x2' => $x + $pkg['length'],
                                'y2' => $y + $pkg['width'],
                                'z2' => $z + $pkg['height']
                            ];

                            $usedVolume += floatval($pkg['volume']);
                            $fittedCount++;
                            $placed = true;
                        }
                    }
                }
            }
        }

        $spaceUtilization = ($usedVolume / $totalVolume) * 100;

        return [
            'fitted_count' => $fittedCount,
            'total_count' => count($packages),
            'space_utilization' => round($spaceUtilization, 2),
            'placements' => $placements
        ];
    }

    /**
     * Check if package can be placed at position
     */
    private function canPlacePackage($pkg, $x, $y, $z, $occupiedSpaces, $capacity) {
        $x2 = $x + $pkg['length'];
        $y2 = $y + $pkg['width'];
        $z2 = $z + $pkg['height'];

        // Check if within vehicle bounds
        if ($x2 > $capacity['length'] || $y2 > $capacity['width'] || $z2 > $capacity['height']) {
            return false;
        }

        // Check for collisions with other packages
        foreach ($occupiedSpaces as $space) {
            if (!($x >= $space['x2'] || $x2 <= $space['x1'] ||
                  $y >= $space['y2'] || $y2 <= $space['y1'] ||
                  $z >= $space['z2'] || $z2 <= $space['z1'])) {
                return false; // Collision detected
            }
        }

        return true;
    }

    /**
     * Get loading plan for route
     */
    public function getLoadingPlan($routeId) {
        $this->db->query("SELECT * FROM loading_plans WHERE route_id = ? ORDER BY created_at DESC LIMIT 1");
        $this->db->bind(1, $routeId);
        $plan = $this->db->single();

        if ($plan) {
            $plan['instructions'] = $this->getLoadingInstructions($plan['id']);
        }

        return $plan;
    }

    /**
     * Get loading instructions
     */
    public function getLoadingInstructions($planId) {
        $this->db->query("SELECT li.*, p.package_number, p.description, p.weight,
                                 p.length, p.width, p.height
                         FROM loading_instructions li
                         LEFT JOIN packages p ON li.package_id = p.id
                         WHERE li.loading_plan_id = ?
                         ORDER BY li.load_sequence ASC");
        $this->db->bind(1, $planId);
        return $this->db->resultSet();
    }

    /**
     * Get AI settings
     */
    private function getAISettings($type) {
        $this->db->query("SELECT * FROM ai_optimization_settings
                         WHERE algorithm_type = ? AND is_active = 1
                         ORDER BY created_at DESC LIMIT 1");
        $this->db->bind(1, $type);
        return $this->db->single();
    }

    /**
     * Get delivery statistics
     */
    public function getDeliveryStats() {
        $stats = [];

        // Pending packages
        $this->db->query("SELECT COUNT(*) as count FROM packages WHERE status = 'pending'");
        $result = $this->db->single();
        $stats['pending_packages'] = $result['count'];

        // Active routes
        $this->db->query("SELECT COUNT(*) as count FROM delivery_routes WHERE status IN ('assigned', 'loading', 'in_progress')");
        $result = $this->db->single();
        $stats['active_routes'] = $result['count'];

        // Today's deliveries
        $this->db->query("SELECT COUNT(*) as count FROM delivery_stops WHERE status = 'delivered' AND DATE(actual_arrival) = CURRENT_DATE");
        $result = $this->db->single();
        $stats['today_deliveries'] = $result['count'];

        // Average route efficiency
        $this->db->query("SELECT AVG(route_efficiency) as avg FROM delivery_routes WHERE status = 'completed'");
        $result = $this->db->single();
        $stats['avg_route_efficiency'] = round($result['avg'] ?? 0, 2);

        return $stats;
    }
}
