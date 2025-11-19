// Constants
const API_URL = window.location.origin + '/digi/passenger_transport';

// App State
let currentUser = null;
let currentView = 'home';
let recentTrips = [];

// Initialize App
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    registerServiceWorker();
    checkOnlineStatus();
});

function initializeApp() {
    // Check if user is already logged in
    const savedUser = localStorage.getItem('passenger_user');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        showMainScreen();
    } else {
        showLoginScreen();
    }

    setupEventListeners();
}

function setupEventListeners() {
    // Login Form
    document.getElementById('login-form').addEventListener('submit', handleLogin);

    // Bottom Navigation
    document.querySelectorAll('.bottom-nav .nav-item').forEach(btn => {
        btn.addEventListener('click', function() {
            switchView(this.dataset.view);
        });
    });

    // Transport Type Selection
    document.querySelectorAll('.transport-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.transport-type-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Book Ride Button
    document.getElementById('book-ride-btn').addEventListener('click', handleBookRide);

    // Use My Location
    document.getElementById('use-my-location').addEventListener('click', useMyLocation);

    // Modals
    document.getElementById('close-booking-modal').addEventListener('click', () => hideModal('booking-modal'));
    document.getElementById('close-trip-modal').addEventListener('click', () => hideModal('trip-modal'));

    // Confirm Booking
    document.getElementById('confirm-booking-btn').addEventListener('click', confirmBooking);

    // Logout
    document.getElementById('logout-btn').addEventListener('click', handleLogout);

    // Profile
    document.getElementById('profile-btn').addEventListener('click', () => switchView('profile'));
}

// Login
function handleLogin(e) {
    e.preventDefault();

    const phone = document.getElementById('phone').value;
    const pin = document.getElementById('pin').value;

    // Simulate login (in production, this would be an API call)
    if (phone && pin) {
        currentUser = {
            id: 1,
            name: 'Fatma Trabelsi',
            phone: phone,
            email: 'fatma@example.com',
            loyalty_points: 125,
            total_trips: 38
        };

        localStorage.setItem('passenger_user', JSON.stringify(currentUser));
        showMainScreen();
    } else {
        alert('Veuillez remplir tous les champs');
    }
}

function handleLogout() {
    if (confirm('Voulez-vous vraiment vous déconnecter?')) {
        localStorage.removeItem('passenger_user');
        currentUser = null;
        showLoginScreen();
    }
}

// Screen Management
function showLoginScreen() {
    document.getElementById('login-screen').classList.add('active');
    document.getElementById('main-screen').classList.remove('active');
}

function showMainScreen() {
    document.getElementById('login-screen').classList.remove('active');
    document.getElementById('main-screen').classList.add('active');

    // Update user info
    document.getElementById('user-name').textContent = currentUser.name;
    document.getElementById('user-points').textContent = `${currentUser.loyalty_points} points fidélité`;

    // Update profile
    document.getElementById('profile-name').textContent = currentUser.name;
    document.getElementById('profile-phone').textContent = currentUser.phone;
    document.getElementById('profile-trips').textContent = currentUser.total_trips;
    document.getElementById('profile-points').textContent = currentUser.loyalty_points;

    // Load recent trips
    loadRecentTrips();
}

// View Switching
function switchView(viewName) {
    // Update bottom nav
    document.querySelectorAll('.bottom-nav .nav-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.view === viewName) {
            item.classList.add('active');
        }
    });

    // Update views
    document.querySelectorAll('.view').forEach(view => {
        view.classList.remove('active');
    });

    document.getElementById(`${viewName}-view`).classList.add('active');
    currentView = viewName;

    // Load content based on view
    if (viewName === 'trips') {
        loadAllTrips();
    }
}

// Geolocation
function useMyLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Reverse geocode using Nominatim (OpenStreetMap)
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('pickup-location').value = data.display_name;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('pickup-location').value = 'Ma position actuelle';
                });
        }, function(error) {
            alert('Impossible d\'obtenir votre position');
        });
    } else {
        alert('La géolocalisation n\'est pas supportée');
    }
}

// Booking
function handleBookRide() {
    const pickup = document.getElementById('pickup-location').value;
    const dropoff = document.getElementById('dropoff-location').value;

    if (!pickup || !dropoff) {
        alert('Veuillez remplir le point de départ et la destination');
        return;
    }

    // Simulate distance and duration calculation
    const distance = (Math.random() * 15 + 2).toFixed(1);
    const duration = Math.ceil(distance * 3);

    // Calculate fare
    const baseFare = 3.00;
    const pricePerKm = 0.80;
    const fare = (baseFare + (distance * pricePerKm)).toFixed(2);

    document.getElementById('estimated-distance').textContent = distance;
    document.getElementById('estimated-duration').textContent = duration;
    document.getElementById('estimated-fare').textContent = fare + ' TND';

    // Show booking modal
    showModal('booking-modal');

    // Initialize map in modal
    setTimeout(initBookingMap, 300);
}

function initBookingMap() {
    const mapElement = document.getElementById('booking-map');
    if (!mapElement._leaflet_id) {
        const map = L.map('booking-map').setView([36.8065, 10.1815], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Add markers for pickup and dropoff
        const pickupMarker = L.marker([36.8065, 10.1815]).addTo(map);
        pickupMarker.bindPopup('Point de départ');

        const dropoffMarker = L.marker([36.82, 10.20]).addTo(map);
        dropoffMarker.bindPopup('Destination');
    }
}

function confirmBooking() {
    const comfortClass = document.getElementById('comfort-class').value;
    const paymentMethod = document.getElementById('payment-method').value;

    // Simulate booking creation
    const booking = {
        id: Date.now(),
        booking_number: 'BOOK' + Date.now(),
        pickup: document.getElementById('pickup-location').value,
        dropoff: document.getElementById('dropoff-location').value,
        status: 'pending',
        fare: document.getElementById('estimated-fare').textContent.replace(' TND', ''),
        comfort_class: comfortClass,
        payment_method: paymentMethod,
        created_at: new Date().toISOString()
    };

    // In production, this would be an API call
    // For now, just save to localStorage
    let bookings = JSON.parse(localStorage.getItem('passenger_bookings') || '[]');
    bookings.unshift(booking);
    localStorage.setItem('passenger_bookings', JSON.stringify(bookings));

    hideModal('booking-modal');

    alert('Réservation confirmée! Recherche d\'un chauffeur en cours...');

    // Simulate driver assignment
    setTimeout(() => {
        booking.status = 'driver_assigned';
        booking.driver_name = 'Ahmed Ben Ali';
        localStorage.setItem('passenger_bookings', JSON.stringify(bookings));

        alert('Chauffeur trouvé! Ahmed Ben Ali arrive dans 5 minutes.');

        // Reload recent trips
        loadRecentTrips();
    }, 2000);
}

// Trips
function loadRecentTrips() {
    const bookings = JSON.parse(localStorage.getItem('passenger_bookings') || '[]');
    recentTrips = bookings.slice(0, 3);

    const container = document.getElementById('recent-trips-list');
    if (recentTrips.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Aucune course récente</p>';
        return;
    }

    container.innerHTML = recentTrips.map(trip => `
        <div class="trip-card" onclick="showTripDetails('${trip.id}')">
            <div class="trip-card-header">
                <strong>${trip.booking_number}</strong>
                <span class="badge badge-${getStatusBadgeClass(trip.status)}">
                    ${getStatusText(trip.status)}
                </span>
            </div>
            <div class="trip-card-body">
                <p><i class="fas fa-circle text-success"></i> ${trip.pickup}</p>
                <p><i class="fas fa-circle text-danger"></i> ${trip.dropoff}</p>
                <p><strong>${trip.fare} TND</strong> • ${new Date(trip.created_at).toLocaleDateString()}</p>
            </div>
        </div>
    `).join('');
}

function loadAllTrips() {
    const bookings = JSON.parse(localStorage.getItem('passenger_bookings') || '[]');

    const container = document.getElementById('trips-list');
    if (bookings.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Aucune course</p>';
        return;
    }

    container.innerHTML = bookings.map(trip => `
        <div class="trip-card" onclick="showTripDetails('${trip.id}')">
            <div class="trip-card-header">
                <strong>${trip.booking_number}</strong>
                <span class="badge badge-${getStatusBadgeClass(trip.status)}">
                    ${getStatusText(trip.status)}
                </span>
            </div>
            <div class="trip-card-body">
                <p><i class="fas fa-circle text-success"></i> ${trip.pickup}</p>
                <p><i class="fas fa-circle text-danger"></i> ${trip.dropoff}</p>
                <p><strong>${trip.fare} TND</strong> • ${new Date(trip.created_at).toLocaleDateString()}</p>
                ${trip.driver_name ? `<p><i class="fas fa-user"></i> Chauffeur: ${trip.driver_name}</p>` : ''}
            </div>
        </div>
    `).join('');
}

function showTripDetails(tripId) {
    const bookings = JSON.parse(localStorage.getItem('passenger_bookings') || '[]');
    const trip = bookings.find(b => b.id == tripId);

    if (!trip) return;

    const content = `
        <h6>Réservation ${trip.booking_number}</h6>
        <p><strong>Statut:</strong> <span class="badge badge-${getStatusBadgeClass(trip.status)}">${getStatusText(trip.status)}</span></p>
        <hr>
        <h6><i class="fas fa-map-marker-alt"></i> Trajet</h6>
        <p><i class="fas fa-circle text-success"></i> ${trip.pickup}</p>
        <p><i class="fas fa-circle text-danger"></i> ${trip.dropoff}</p>
        <hr>
        ${trip.driver_name ? `<p><strong>Chauffeur:</strong> ${trip.driver_name}</p>` : ''}
        <p><strong>Montant:</strong> ${trip.fare} TND</p>
        <p><strong>Paiement:</strong> ${trip.payment_method}</p>
        <p><strong>Date:</strong> ${new Date(trip.created_at).toLocaleString()}</p>
    `;

    document.getElementById('trip-details-content').innerHTML = content;
    showModal('trip-modal');
}

// Modal Management
function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Utilities
function getStatusBadgeClass(status) {
    const classes = {
        'pending': 'warning',
        'driver_assigned': 'primary',
        'in_progress': 'warning',
        'completed': 'success',
        'cancelled': 'secondary'
    };
    return classes[status] || 'secondary';
}

function getStatusText(status) {
    const texts = {
        'pending': 'En attente',
        'driver_assigned': 'Chauffeur assigné',
        'in_progress': 'En cours',
        'completed': 'Terminé',
        'cancelled': 'Annulé'
    };
    return texts[status] || status;
}

// Service Worker
function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('./service-worker.js')
            .then(reg => console.log('Service Worker registered'))
            .catch(err => console.log('Service Worker registration failed:', err));
    }
}

// Online/Offline Status
function checkOnlineStatus() {
    const indicator = document.getElementById('offline-indicator');

    function updateStatus() {
        if (navigator.onLine) {
            indicator.style.display = 'none';
        } else {
            indicator.style.display = 'block';
        }
    }

    window.addEventListener('online', updateStatus);
    window.addEventListener('offline', updateStatus);

    updateStatus();
}
