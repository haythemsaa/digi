<?php require_once '../app/views/includes/header.php'; ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0"><i class="fas fa-plus"></i> Créer un Arrêt de Bus</h1>
            <p class="text-muted">Ajouter un nouveau point d'arrêt</p>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo APP_URL; ?>/passenger_transport/busStops" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux Arrêts
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo APP_URL; ?>/passenger_transport/createBusStop">
        <div class="row">
            <!-- Left Column: Map -->
            <div class="col-md-7 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0"><i class="fas fa-map"></i> Sélectionner l'Emplacement (OpenStreetMap)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="stopMap" style="height: 500px; width: 100%;"></div>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Cliquez sur la carte pour placer le marqueur d'arrêt
                        </small>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form -->
            <div class="col-md-5">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> Informations de l'Arrêt</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="stop_name">Nom de l'Arrêt <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="stop_name" name="stop_name"
                                   placeholder="Ex: Place de la République" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Adresse</label>
                            <textarea class="form-control" id="address" name="address" rows="2"
                                      placeholder="Adresse complète..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude">Latitude <span class="text-danger">*</span></label>
                                    <input type="number" step="0.000001" class="form-control" id="latitude" name="latitude"
                                           placeholder="36.8065" required readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude">Longitude <span class="text-danger">*</span></label>
                                    <input type="number" step="0.000001" class="form-control" id="longitude" name="longitude"
                                           placeholder="10.1815" required readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="zone">Zone Tarifaire</label>
                            <input type="text" class="form-control" id="zone" name="zone"
                                   placeholder="Ex: Centre-Ville">
                        </div>

                        <div class="form-group">
                            <label for="nearby_landmarks">Points de Repère</label>
                            <textarea class="form-control" id="nearby_landmarks" name="nearby_landmarks" rows="2"
                                      placeholder="Près de la mairie, face au marché..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0"><i class="fas fa-tools"></i> Équipements</h6>
                    </div>
                    <div class="card-body">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="has_shelter" name="has_shelter" value="1">
                            <label class="custom-control-label" for="has_shelter">
                                <i class="fas fa-home text-primary"></i> Abri
                            </label>
                        </div>

                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="has_bench" name="has_bench" value="1">
                            <label class="custom-control-label" for="has_bench">
                                <i class="fas fa-chair text-success"></i> Banc
                            </label>
                        </div>

                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="has_lighting" name="has_lighting" value="1">
                            <label class="custom-control-label" for="has_lighting">
                                <i class="fas fa-lightbulb text-warning"></i> Éclairage
                            </label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_accessible" name="is_accessible" value="1">
                            <label class="custom-control-label" for="is_accessible">
                                <i class="fas fa-wheelchair text-info"></i> Accessible PMR
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-save"></i> Créer l'Arrêt
                        </button>
                        <a href="<?php echo APP_URL; ?>/passenger_transport/busStops" class="btn btn-secondary btn-block">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map centered on Tunis
var map = L.map('stopMap').setView([36.8065, 10.1815], 13);

// Add OpenStreetMap tile layer (FREE!)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(map);

// Custom icon for new bus stop
var newStopIcon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIxOCIgZmlsbD0iIzI4YTc0NSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIzIi8+PHRleHQgeD0iMjAiIHk9IjI3IiBmb250LXNpemU9IjIwIiBmb250LWZhbWlseT0iQXJpYWwiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5qPPC90ZXh0Pjwvc3ZnPg==',
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40]
});

var currentMarker = null;

// Click on map to place marker
map.on('click', function(e) {
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;

    // Remove existing marker
    if (currentMarker) {
        map.removeLayer(currentMarker);
    }

    // Add new marker
    currentMarker = L.marker([lat, lng], {
        icon: newStopIcon,
        draggable: true
    }).addTo(map);

    currentMarker.bindPopup(`
        <div class="text-center">
            <h6 class="text-success">Nouvel Arrêt</h6>
            <small>Déplacer le marqueur pour ajuster</small>
        </div>
    `).openPopup();

    // Update form fields
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);

    // Update coordinates when marker is dragged
    currentMarker.on('dragend', function(event) {
        var position = event.target.getLatLng();
        document.getElementById('latitude').value = position.lat.toFixed(6);
        document.getElementById('longitude').value = position.lng.toFixed(6);
    });

    // Reverse geocoding with Nominatim (OpenStreetMap's free geocoding service)
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(response => response.json())
        .then(data => {
            if (data.display_name) {
                document.getElementById('address').value = data.display_name;
            }
        })
        .catch(error => console.log('Geocoding error:', error));
});

// Geolocation button
L.control.locate = L.Control.extend({
    onAdd: function(map) {
        var btn = L.DomUtil.create('button', 'leaflet-bar');
        btn.innerHTML = '<i class="fas fa-crosshairs"></i>';
        btn.style.backgroundColor = 'white';
        btn.style.width = '30px';
        btn.style.height = '30px';
        btn.style.cursor = 'pointer';
        btn.title = 'Ma position';

        btn.onclick = function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    map.setView([lat, lng], 16);

                    // Trigger click to place marker
                    map.fire('click', {
                        latlng: L.latLng(lat, lng)
                    });
                });
            }
        };

        return btn;
    }
});

new L.control.locate({ position: 'topleft' }).addTo(map);
</script>

<style>
.leaflet-control-locate {
    margin-top: 10px;
}
</style>

<?php require_once '../app/views/includes/footer.php'; ?>
