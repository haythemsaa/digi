<?php
$data['title'] = 'Dashboard';
$data['page_title'] = 'Dashboard';
$data['active_menu'] = 'dashboard';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <!-- Statistics Cards -->
            <div class="row">
                <!-- Total Vehicles -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-2">Total Vehicles</h6>
                                    <h2 class="mb-0"><?php echo $data['stats']['total_vehicles'] ?? 0; ?></h2>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-car"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo APP_URL; ?>/vehicles" class="text-white text-decoration-none">
                                    <small>View all <i class="fas fa-arrow-right ms-1"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Vehicles -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-2">Active Vehicles</h6>
                                    <h2 class="mb-0"><?php echo $data['stats']['active_vehicles'] ?? 0; ?></h2>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo APP_URL; ?>/tracking" class="text-white text-decoration-none">
                                    <small>Track now <i class="fas fa-arrow-right ms-1"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-2">In Maintenance</h6>
                                    <h2 class="mb-0"><?php echo $data['stats']['maintenance_vehicles'] ?? 0; ?></h2>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo APP_URL; ?>/maintenance" class="text-white text-decoration-none">
                                    <small>View details <i class="fas fa-arrow-right ms-1"></i></small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 mb-2">Total Users</h6>
                                    <h2 class="mb-0"><?php echo $data['stats']['total_users'] ?? 0; ?></h2>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="<?php echo APP_URL; ?>/users" class="text-white text-decoration-none">
                                        <small>Manage users <i class="fas fa-arrow-right ms-1"></i></small>
                                    </a>
                                <?php else: ?>
                                    <small>&nbsp;</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mt-4">
                <!-- Fleet Status Chart -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Fleet Activity (Last 7 Days)</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="fleetActivityChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Fuel Consumption Chart -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-gas-pump me-2"></i>Fuel Consumption</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="fuelChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Activity -->
            <div class="row mt-4">
                <!-- Quick Actions -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?php echo APP_URL; ?>/vehicles/add" class="btn btn-outline-primary">
                                    <i class="fas fa-plus-circle me-2"></i>Add New Vehicle
                                </a>
                                <a href="<?php echo APP_URL; ?>/transport/add" class="btn btn-outline-success">
                                    <i class="fas fa-shipping-fast me-2"></i>Create Transport Order
                                </a>
                                <a href="<?php echo APP_URL; ?>/maintenance/add" class="btn btn-outline-warning">
                                    <i class="fas fa-wrench me-2"></i>Schedule Maintenance
                                </a>
                                <a href="<?php echo APP_URL; ?>/tracking" class="btn btn-outline-info">
                                    <i class="fas fa-map-marked-alt me-2"></i>Track Vehicles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-success text-white rounded-circle p-3">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Maintenance Completed</h6>
                                            <p class="mb-0 text-muted small">Vehicle ABC-123 - Oil change completed</p>
                                        </div>
                                        <div class="text-muted small">2h ago</div>
                                    </div>
                                </div>

                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle p-3">
                                                <i class="fas fa-shipping-fast"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">New Transport Order</h6>
                                            <p class="mb-0 text-muted small">Order #TO-2024-001 created</p>
                                        </div>
                                        <div class="text-muted small">5h ago</div>
                                    </div>
                                </div>

                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-warning text-white rounded-circle p-3">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Speed Alert</h6>
                                            <p class="mb-0 text-muted small">Vehicle XYZ-789 exceeded speed limit</p>
                                        </div>
                                        <div class="text-muted small">8h ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fleet Activity Chart
    const fleetActivityCtx = document.getElementById('fleetActivityChart').getContext('2d');
    new Chart(fleetActivityCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Active Vehicles',
                data: [12, 15, 13, 18, 16, 14, 17],
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Distance (km)',
                data: [850, 920, 780, 1100, 950, 880, 1020],
                borderColor: 'rgb(118, 75, 162)',
                backgroundColor: 'rgba(118, 75, 162, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Fuel Consumption Doughnut Chart
    const fuelCtx = document.getElementById('fuelChart').getContext('2d');
    new Chart(fuelCtx, {
        type: 'doughnut',
        data: {
            labels: ['Diesel', 'Gasoline', 'Electric'],
            datasets: [{
                data: [65, 30, 5],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
