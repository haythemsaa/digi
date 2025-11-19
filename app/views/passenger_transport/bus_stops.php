<?php require_once '../app/views/includes/header.php'; ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-map-marked-alt"></i> Arrêts de Bus</h1>
            <p class="text-muted">Gestion des points d'arrêt du réseau</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/createBusStop" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouvel Arrêt
            </a>
            <a href="<?php echo APP_URL; ?>/passenger_transport/busRoutes" class="btn btn-info">
                <i class="fas fa-bus"></i> Lignes de Bus
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Map View -->
        <div class="col-md-7 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-map"></i> Carte des Arrêts (OpenStreetMap)</h6>
                </div>
                <div class="card-body p-0">
                    <div id="stopsMap" style="height: 600px; width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Stops List -->
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-list"></i> Liste des Arrêts (<?php echo count($data['stops']); ?>)
                    </h6>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php if (!empty($data['stops'])): ?>
                        <div class="list-group">
                            <?php foreach ($data['stops'] as $stop): ?>
                                <div class="list-group-item list-group-item-action stop-item"
                                     data-lat="<?php echo $stop['latitude']; ?>"
                                     data-lng="<?php echo $stop['longitude']; ?>"
                                     data-stop-id="<?php echo $stop['id']; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($stop['stop_code']); ?></span>
                                            <?php echo htmlspecialchars($stop['stop_name']); ?>
                                        </h6>
                                        <small>
                                            <?php if ($stop['is_active']): ?>
                                                <span class="badge badge-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactif</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <p class="mb-1">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($stop['address'] ?? 'Adresse non spécifiée'); ?>
                                        </small>
                                    </p>
                                    <div class="mb-1">
                                        <small>
                                            <?php if ($stop['zone']): ?>
                                                <span class="badge badge-info">Zone: <?php echo htmlspecialchars($stop['zone']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($stop['has_shelter']): ?>
                                                <span class="badge badge-secondary"><i class="fas fa-home"></i> Abri</span>
                                            <?php endif; ?>
                                            <?php if ($stop['has_bench']): ?>
                                                <span class="badge badge-secondary"><i class="fas fa-chair"></i> Banc</span>
                                            <?php endif; ?>
                                            <?php if ($stop['has_lighting']): ?>
                                                <span class="badge badge-secondary"><i class="fas fa-lightbulb"></i> Éclairage</span>
                                            <?php endif; ?>
                                            <?php if ($stop['is_accessible']): ?>
                                                <span class="badge badge-success"><i class="fas fa-wheelchair"></i> PMR</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-crosshairs"></i>
                                        GPS: <?php echo $stop['latitude']; ?>, <?php echo $stop['longitude']; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Aucun arrêt de bus enregistré.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map centered on Tunis
var map = L.map('stopsMap').setView([36.8065, 10.1815], 12);

// Add OpenStreetMap tile layer (FREE!)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(map);

// Custom icon for bus stops
var busStopIcon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48Y2lyY2xlIGN4PSIxNiIgY3k9IjE2IiByPSIxNCIgZmlsbD0iIzAwN2JmZiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIi8+PHRleHQgeD0iMTYiIHk9IjIxIiBmb250LXNpemU9IjE2IiBmb250LWZhbWlseT0iQXJpYWwiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5qPPC90ZXh0Pjwvc3ZnPg==',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

var markers = [];

// Add markers for all stops
<?php foreach ($data['stops'] as $stop): ?>
    <?php if ($stop['latitude'] && $stop['longitude']): ?>
        var marker = L.marker([<?php echo $stop['latitude']; ?>, <?php echo $stop['longitude']; ?>], {
            icon: busStopIcon
        }).addTo(map);

        marker.bindPopup(`
            <div class="text-center">
                <h6><span class="badge badge-primary"><?php echo htmlspecialchars($stop['stop_code']); ?></span></h6>
                <strong><?php echo htmlspecialchars($stop['stop_name']); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($stop['address'] ?? ''); ?></small><br>
                <?php if ($stop['zone']): ?>
                    <span class="badge badge-info mt-2">Zone: <?php echo htmlspecialchars($stop['zone']); ?></span>
                <?php endif; ?>
                <hr class="my-2">
                <div class="btn-group btn-group-sm">
                    <?php if ($stop['has_shelter']): ?><span class="badge badge-secondary"><i class="fas fa-home"></i></span><?php endif; ?>
                    <?php if ($stop['has_bench']): ?><span class="badge badge-secondary"><i class="fas fa-chair"></i></span><?php endif; ?>
                    <?php if ($stop['has_lighting']): ?><span class="badge badge-secondary"><i class="fas fa-lightbulb"></i></span><?php endif; ?>
                    <?php if ($stop['is_accessible']): ?><span class="badge badge-success"><i class="fas fa-wheelchair"></i></span><?php endif; ?>
                </div>
            </div>
        `);

        markers.push({
            id: <?php echo $stop['id']; ?>,
            marker: marker
        });
    <?php endif; ?>
<?php endforeach; ?>

// Fit map to show all markers
if (markers.length > 0) {
    var group = new L.featureGroup(markers.map(m => m.marker));
    map.fitBounds(group.getBounds().pad(0.1));
}

// Click on list item to center map on stop
document.querySelectorAll('.stop-item').forEach(function(item) {
    item.addEventListener('click', function() {
        var lat = parseFloat(this.dataset.lat);
        var lng = parseFloat(this.dataset.lng);
        var stopId = parseInt(this.dataset.stopId);

        // Center map on stop
        map.setView([lat, lng], 16);

        // Open popup
        var marker = markers.find(m => m.id === stopId);
        if (marker) {
            marker.marker.openPopup();
        }

        // Highlight item
        document.querySelectorAll('.stop-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>

<style>
.stop-item {
    cursor: pointer;
    transition: all 0.2s;
}
.stop-item:hover {
    background-color: #f8f9fc;
}
.stop-item.active {
    border-left: 4px solid #007bff;
    background-color: #e3f2fd;
}
</style>

<?php require_once '../app/views/includes/footer.php'; ?>
