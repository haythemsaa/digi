<?php
$data['title'] = 'GPS Tracking';
require_once APP_PATH . '/views/includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once APP_PATH . '/views/includes/sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 main-content">
            <?php require_once APP_PATH . '/views/includes/navbar.php'; ?>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6>Active Vehicles</h6>
                            <h2><?php echo count(array_filter($data['positions'], function($p) { return $p['latitude']; })); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6>Moving</h6>
                            <h2><?php echo count(array_filter($data['positions'], function($p) { return $p['speed'] > 5; })); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6>Idle</h6>
                            <h2><?php echo count(array_filter($data['positions'], function($p) { return $p['speed'] <= 5 && $p['latitude']; })); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <h6>Geofences</h6>
                            <h2><?php echo count($data['geofences']); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Real-time Vehicle Tracking</h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshMap()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <a href="<?php echo APP_URL; ?>/tracking/geofences" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-draw-polygon"></i> Geofences
                        </a>
                        <a href="<?php echo APP_URL; ?>/tracking/alerts" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-exclamation-triangle"></i> Alerts
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 600px;"></div>
                </div>
            </div>

            <!-- Vehicle List -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Vehicle Status</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Speed</th>
                                    <th>Fuel</th>
                                    <th>Engine</th>
                                    <th>Last Update</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['positions'] as $pos): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $pos['registration_number']; ?></strong><br>
                                            <small class="text-muted"><?php echo $pos['brand'] . ' ' . $pos['model']; ?></small>
                                        </td>
                                        <td>
                                            <?php if ($pos['latitude']): ?>
                                                <span class="badge bg-<?php echo $pos['speed'] > 80 ? 'danger' : ($pos['speed'] > 5 ? 'success' : 'secondary'); ?>">
                                                    <?php echo round($pos['speed'], 1); ?> km/h
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($pos['fuel_level']): ?>
                                                <div class="progress" style="width: 60px;">
                                                    <div class="progress-bar bg-<?php echo $pos['fuel_level'] < 20 ? 'danger' : 'success'; ?>"
                                                         style="width: <?php echo $pos['fuel_level']; ?>%">
                                                        <?php echo round($pos['fuel_level']); ?>%
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($pos['engine_status'])): ?>
                                                <i class="fas fa-circle text-<?php echo $pos['engine_status'] ? 'success' : 'danger'; ?>"></i>
                                                <?php echo $pos['engine_status'] ? 'ON' : 'OFF'; ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($pos['timestamp']): ?>
                                                <small><?php echo date('d/m/Y H:i', strtotime($pos['timestamp'])); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">No data</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($pos['latitude']): ?>
                                                <button class="btn btn-sm btn-primary" onclick="centerOnVehicle(<?php echo $pos['latitude']; ?>, <?php echo $pos['longitude']; ?>)">
                                                    <i class="fas fa-crosshairs"></i>
                                                </button>
                                                <a href="<?php echo APP_URL; ?>/tracking/history/<?php echo $pos['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize map
var map = L.map('map').setView([36.8065, 10.1815], 12); // Tunis, Tunisia

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Vehicle markers
var markers = {};

// Add vehicles to map
<?php foreach ($data['positions'] as $pos): ?>
    <?php if ($pos['latitude'] && $pos['longitude']): ?>
        var marker = L.marker([<?php echo $pos['latitude']; ?>, <?php echo $pos['longitude']; ?>], {
            icon: L.divIcon({
                className: 'vehicle-marker',
                html: '<div style="background: <?php echo $pos['speed'] > 5 ? '#28a745' : '#6c757d'; ?>; color: white; padding: 5px 10px; border-radius: 15px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="fas fa-car"></i> <?php echo $pos['registration_number']; ?></div>',
                iconSize: [120, 40]
            })
        }).addTo(map);

        marker.bindPopup(`
            <strong><?php echo $pos['registration_number']; ?></strong><br>
            <?php echo $pos['brand'] . ' ' . $pos['model']; ?><br>
            <hr class="my-1">
            <small>
                <i class="fas fa-tachometer-alt"></i> Speed: <?php echo round($pos['speed'], 1); ?> km/h<br>
                <?php if ($pos['fuel_level']): ?>
                <i class="fas fa-gas-pump"></i> Fuel: <?php echo round($pos['fuel_level']); ?>%<br>
                <?php endif; ?>
                <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($pos['timestamp'])); ?>
            </small><br>
            <a href="<?php echo APP_URL; ?>/tracking/history/<?php echo $pos['id']; ?>" class="btn btn-sm btn-primary mt-2">
                <i class="fas fa-history"></i> History
            </a>
        `);

        markers[<?php echo $pos['id']; ?>] = marker;
    <?php endif; ?>
<?php endforeach; ?>

// Add geofences
<?php foreach ($data['geofences'] as $gf): ?>
    <?php
    $coords = json_decode($gf['coordinates'], true);
    if ($gf['type'] === 'circle' && isset($coords['lat']) && isset($coords['lng'])):
    ?>
        L.circle([<?php echo $coords['lat']; ?>, <?php echo $coords['lng']; ?>], {
            radius: <?php echo $gf['radius']; ?>,
            color: '<?php echo $gf['color']; ?>',
            fillColor: '<?php echo $gf['color']; ?>',
            fillOpacity: 0.2
        }).addTo(map).bindPopup('<strong><?php echo $gf['name']; ?></strong><br><?php echo $gf['description']; ?>');
    <?php endif; ?>
<?php endforeach; ?>

// Center on vehicle
function centerOnVehicle(lat, lng) {
    map.setView([lat, lng], 15);
}

// Refresh map data
function refreshMap() {
    showLoading();
    fetch('<?php echo APP_URL; ?>/tracking/getPositions')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update markers
                data.positions.forEach(pos => {
                    if (pos.latitude && pos.longitude && markers[pos.id]) {
                        markers[pos.id].setLatLng([pos.latitude, pos.longitude]);
                        // Update popup
                        markers[pos.id].setPopupContent(`
                            <strong>${pos.registration_number}</strong><br>
                            ${pos.brand} ${pos.model}<br>
                            <hr class="my-1">
                            <small>
                                <i class="fas fa-tachometer-alt"></i> Speed: ${Math.round(pos.speed * 10) / 10} km/h<br>
                                ${pos.fuel_level ? `<i class="fas fa-gas-pump"></i> Fuel: ${Math.round(pos.fuel_level)}%<br>` : ''}
                                <i class="fas fa-clock"></i> ${new Date(pos.timestamp).toLocaleTimeString()}
                            </small>
                        `);
                    }
                });
                showToast('Map refreshed', 'success');
            }
            hideLoading();
        })
        .catch(error => {
            console.error('Error:', error);
            hideLoading();
        });
}

// Auto-refresh every 30 seconds
setInterval(refreshMap, 30000);
</script>

<?php require_once APP_PATH . '/views/includes/footer.php'; ?>
