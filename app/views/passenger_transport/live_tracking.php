<?php require_once '../app/views/includes/header.php'; ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0"><i class="fas fa-satellite-dish"></i> Suivi en Temps Réel</h1>
            <p class="text-muted">Tracking GPS des courses actives</p>
        </div>
        <div class="col-md-4 text-right">
            <div class="btn-group">
                <button class="btn btn-success" id="autoRefreshBtn">
                    <i class="fas fa-sync-alt"></i> Auto-Refresh: <span id="refreshStatus">ON</span>
                </button>
                <button class="btn btn-info" onclick="refreshTracking()">
                    <i class="fas fa-redo"></i> Actualiser
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Map -->
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-map"></i> Carte de Suivi (OpenStreetMap)</h6>
                </div>
                <div class="card-body p-0">
                    <div id="trackingMap" style="height: 700px; width: 100%;"></div>
                </div>
                <div class="card-footer">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h5 class="mb-0 text-warning" id="activeCount">0</h5>
                            <small>Courses Actives</small>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-0 text-success" id="availableCount">0</h5>
                            <small>Chauffeurs Disponibles</small>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-0 text-info" id="completedToday">0</h5>
                            <small>Complétées Aujourd'hui</small>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-0 text-primary" id="totalRevenue">0 TND</h5>
                            <small>Revenu du Jour</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Rides List -->
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0"><i class="fas fa-car"></i> Courses en Cours</h6>
                </div>
                <div class="card-body p-0" style="max-height: 700px; overflow-y: auto;" id="activeRidesList">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2">Chargement...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map centered on Tunis
var map = L.map('trackingMap').setView([36.8065, 10.1815], 12);

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(map);

// Icons for different vehicle types
var taxiIcon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48Y2lyY2xlIGN4PSIxNiIgY3k9IjE2IiByPSIxNCIgZmlsbD0iI2ZmYzkwNyIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIi8+PHRleHQgeD0iMTYiIHk9IjIxIiBmb250LXNpemU9IjE2IiBmb250LWZhbWlseT0iQXJpYWwiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5qWPC90ZXh0Pjwvc3ZnPg==',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

var busIcon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48Y2lyY2xlIGN4PSIxNiIgY3k9IjE2IiByPSIxNCIgZmlsbD0iIzAwN2JmZiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIi8+PHRleHQgeD0iMTYiIHk9IjIxIiBmb250LXNpemU9IjE2IiBmb250LWZhbWlseT0iQXJpYWwiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7wn5qMPC90ZXh0Pjwvc3ZnPg==',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
});

var availableDriverIcon = L.icon({
    iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSIxMCIgZmlsbD0iIzI4YTc0NSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIi8+PHRleHQgeD0iMTIiIHk9IjE2IiBmb250LXNpemU9IjEyIiBmb250LWZhbWlseT0iQXJpYWwiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7inJM8L3RleHQ+PC9zdmc+',
    iconSize: [24, 24],
    iconAnchor: [12, 24],
    popupAnchor: [0, -24]
});

var markers = {};
var autoRefresh = true;
var refreshInterval;

// Simulated data (in production, this would come from real API)
function getActiveRides() {
    // This would be an AJAX call to get real data
    // For demonstration, returning simulated data
    return [
        {
            id: 1,
            booking_number: 'BOOK20241119001',
            type: 'taxi',
            driver_name: 'Ahmed Ben Ali',
            passenger_name: 'Fatma Trabelsi',
            pickup: 'Avenue Habib Bourguiba',
            dropoff: 'Aéroport Carthage',
            status: 'in_progress',
            lat: 36.8065 + (Math.random() - 0.5) * 0.1,
            lng: 10.1815 + (Math.random() - 0.5) * 0.1,
            estimated_arrival: '15 min'
        },
        {
            id: 2,
            booking_number: 'BOOK20241119002',
            type: 'taxi',
            driver_name: 'Mohamed Saidi',
            passenger_name: 'Sarah Chouchane',
            pickup: 'Gare Centrale',
            dropoff: 'La Marsa',
            status: 'driver_arrived',
            lat: 36.8165 + (Math.random() - 0.5) * 0.1,
            lng: 10.1915 + (Math.random() - 0.5) * 0.1,
            estimated_arrival: '2 min'
        },
        {
            id: 3,
            booking_number: 'BOOK20241119003',
            type: 'bus',
            driver_name: 'Karim Jebali',
            route_name: 'Ligne 12 - Centre-Ville',
            status: 'in_progress',
            lat: 36.7965 + (Math.random() - 0.5) * 0.1,
            lng: 10.1715 + (Math.random() - 0.5) * 0.1,
            passengers: 23
        }
    ];
}

function getAvailableDrivers() {
    // Simulated available drivers
    return [
        { id: 101, name: 'Ali Mansour', lat: 36.81 + (Math.random() - 0.5) * 0.05, lng: 10.19 + (Math.random() - 0.5) * 0.05 },
        { id: 102, name: 'Sami Bouaziz', lat: 36.80 + (Math.random() - 0.5) * 0.05, lng: 10.17 + (Math.random() - 0.5) * 0.05 },
        { id: 103, name: 'Youssef Mejri', lat: 36.82 + (Math.random() - 0.5) * 0.05, lng: 10.18 + (Math.random() - 0.5) * 0.05 }
    ];
}

function updateTracking() {
    // Clear existing markers
    Object.values(markers).forEach(m => map.removeLayer(m));
    markers = {};

    // Get active rides
    var activeRides = getActiveRides();
    var availableDrivers = getAvailableDrivers();

    // Update stats
    document.getElementById('activeCount').textContent = activeRides.length;
    document.getElementById('availableCount').textContent = availableDrivers.length;
    document.getElementById('completedToday').textContent = Math.floor(Math.random() * 50) + 20;
    document.getElementById('totalRevenue').textContent = (Math.random() * 5000 + 2000).toFixed(2) + ' TND';

    // Add markers for active rides
    activeRides.forEach(ride => {
        var icon = ride.type === 'taxi' ? taxiIcon : busIcon;
        var marker = L.marker([ride.lat, ride.lng], { icon: icon }).addTo(map);

        var popupContent = `
            <div style="min-width: 200px;">
                <h6><span class="badge badge-warning">${ride.booking_number}</span></h6>
                <p class="mb-1"><strong>Chauffeur:</strong> ${ride.driver_name}</p>
                ${ride.passenger_name ? `<p class="mb-1"><strong>Passager:</strong> ${ride.passenger_name}</p>` : ''}
                ${ride.route_name ? `<p class="mb-1"><strong>Ligne:</strong> ${ride.route_name}</p>` : ''}
                ${ride.pickup ? `<p class="mb-1"><small><i class="fas fa-circle text-success"></i> ${ride.pickup}</small></p>` : ''}
                ${ride.dropoff ? `<p class="mb-1"><small><i class="fas fa-circle text-danger"></i> ${ride.dropoff}</small></p>` : ''}
                ${ride.estimated_arrival ? `<p class="mb-0"><strong>ETA:</strong> ${ride.estimated_arrival}</p>` : ''}
                ${ride.passengers ? `<p class="mb-0"><strong>Passagers:</strong> ${ride.passengers}</p>` : ''}
            </div>
        `;

        marker.bindPopup(popupContent);
        markers[`ride_${ride.id}`] = marker;
    });

    // Add markers for available drivers
    availableDrivers.forEach(driver => {
        var marker = L.marker([driver.lat, driver.lng], { icon: availableDriverIcon }).addTo(map);
        marker.bindPopup(`
            <div class="text-center">
                <h6 class="text-success">Disponible</h6>
                <p class="mb-0"><strong>${driver.name}</strong></p>
            </div>
        `);
        markers[`driver_${driver.id}`] = marker;
    });

    // Update active rides list
    updateActiveRidesList(activeRides);
}

function updateActiveRidesList(rides) {
    var listHtml = '';

    if (rides.length === 0) {
        listHtml = `
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-muted">Aucune course active</p>
            </div>
        `;
    } else {
        rides.forEach(ride => {
            var statusClass = ride.status === 'in_progress' ? 'warning' : 'info';
            var statusText = ride.status === 'in_progress' ? 'En cours' : 'Chauffeur arrivé';

            listHtml += `
                <div class="border-bottom p-3 ride-item" data-lat="${ride.lat}" data-lng="${ride.lng}">
                    <div class="d-flex justify-content-between mb-2">
                        <small><strong>${ride.booking_number}</strong></small>
                        <span class="badge badge-${statusClass}">${statusText}</span>
                    </div>
                    <p class="mb-1"><small><i class="fas fa-user"></i> ${ride.driver_name}</small></p>
                    ${ride.passenger_name ? `<p class="mb-1"><small><i class="fas fa-user-friends"></i> ${ride.passenger_name}</small></p>` : ''}
                    ${ride.estimated_arrival ? `<p class="mb-0"><small class="text-muted"><i class="fas fa-clock"></i> ETA: ${ride.estimated_arrival}</small></p>` : ''}
                </div>
            `;
        });
    }

    document.getElementById('activeRidesList').innerHTML = listHtml;

    // Add click handlers to zoom to ride
    document.querySelectorAll('.ride-item').forEach(item => {
        item.addEventListener('click', function() {
            var lat = parseFloat(this.dataset.lat);
            var lng = parseFloat(this.dataset.lng);
            map.setView([lat, lng], 15);
        });
    });
}

function refreshTracking() {
    updateTracking();
}

function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    document.getElementById('refreshStatus').textContent = autoRefresh ? 'ON' : 'OFF';

    if (autoRefresh) {
        refreshInterval = setInterval(updateTracking, 10000); // Refresh every 10 seconds
        document.getElementById('autoRefreshBtn').classList.remove('btn-secondary');
        document.getElementById('autoRefreshBtn').classList.add('btn-success');
    } else {
        clearInterval(refreshInterval);
        document.getElementById('autoRefreshBtn').classList.remove('btn-success');
        document.getElementById('autoRefreshBtn').classList.add('btn-secondary');
    }
}

// Initial load
updateTracking();

// Auto refresh every 10 seconds
refreshInterval = setInterval(updateTracking, 10000);

// Toggle auto-refresh
document.getElementById('autoRefreshBtn').addEventListener('click', toggleAutoRefresh);
</script>

<style>
.ride-item {
    cursor: pointer;
    transition: background-color 0.2s;
}
.ride-item:hover {
    background-color: #f8f9fc;
}
</style>

<?php require_once '../app/views/includes/footer.php'; ?>
