<?php
$data['title'] = 'GPS Alerts';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <h4 class="mb-4"><i class="fas fa-exclamation-triangle me-2"></i>GPS Alerts</h4>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6>Speed Alerts</h6>
                            <h2><?php echo isset($data['speed_alerts']) ? count($data['speed_alerts']) : 0; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Geofence Alerts</h6>
                            <h2><?php echo isset($data['geofence_alerts']) ? count($data['geofence_alerts']) : 0; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6>Today's Alerts</h6>
                            <h2><?php echo isset($data['today_count']) ? $data['today_count'] : 0; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h6>This Week</h6>
                            <h2><?php echo isset($data['week_count']) ? $data['week_count'] : 0; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Speed Alerts -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Speed Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Vehicle</th>
                                    <th>Speed</th>
                                    <th>Limit</th>
                                    <th>Location</th>
                                    <th>Exceeded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($data['speed_alerts']) && count($data['speed_alerts']) > 0): ?>
                                    <?php foreach ($data['speed_alerts'] as $alert): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($alert['alert_time'])); ?></td>
                                            <td><strong><?php echo $alert['registration_number']; ?></strong></td>
                                            <td><span class="badge bg-danger"><?php echo round($alert['speed']); ?> km/h</span></td>
                                            <td><?php echo $alert['speed_limit']; ?> km/h</td>
                                            <td>
                                                <small>
                                                    <?php echo round($alert['latitude'], 6); ?>, <?php echo round($alert['longitude'], 6); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="text-danger">
                                                    +<?php echo round($alert['speed'] - $alert['speed_limit']); ?> km/h
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No speed alerts found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Geofence Alerts -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-draw-polygon me-2"></i>Geofence Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Vehicle</th>
                                    <th>Geofence</th>
                                    <th>Alert Type</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($data['geofence_alerts']) && count($data['geofence_alerts']) > 0): ?>
                                    <?php foreach ($data['geofence_alerts'] as $alert): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($alert['alert_time'])); ?></td>
                                            <td><strong><?php echo $alert['registration_number']; ?></strong></td>
                                            <td><?php echo $alert['geofence_name']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $alert['alert_type'] === 'entry' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($alert['alert_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php echo round($alert['latitude'], 6); ?>, <?php echo round($alert['longitude'], 6); ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No geofence alerts found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
