// Configuration
const API_URL = window.location.origin + '/smart_delivery';
const CACHE_NAME = 'delivery-ai-v1';

// App State
let currentUser = null;
let currentRoute = null;
let currentStop = null;
let signaturePad = null;

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
    checkLoginStatus();
    registerServiceWorker();
});

// Initialize
function initializeApp() {
    // Login form
    document.getElementById('login-form').addEventListener('submit', handleLogin);

    // Logout
    document.getElementById('logout-btn').addEventListener('submit', handleLogout);

    // Navigation
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', (e) => {
            const view = e.currentTarget.dataset.view;
            switchView(view);
        });
    });

    // Refresh routes
    document.getElementById('refresh-routes').addEventListener('click', loadRoutes);

    // Modal close buttons
    document.getElementById('close-route-detail').addEventListener('click', () => {
        closeModal('route-detail-modal');
    });

    document.getElementById('close-delivery').addEventListener('click', () => {
        closeModal('delivery-modal');
    });

    // Signature pad
    const canvas = document.getElementById('signature-pad');
    if (canvas) {
        signaturePad = new SignaturePad(canvas);

        document.getElementById('clear-signature').addEventListener('click', () => {
            signaturePad.clear();
        });
    }

    // Photo upload
    document.getElementById('delivery-photo').addEventListener('change', handlePhotoUpload);

    // Delivery actions
    document.getElementById('mark-delivered-btn').addEventListener('click', () => markDelivery('delivered'));
    document.getElementById('mark-failed-btn').addEventListener('click', () => markDelivery('failed'));

    // Check network status
    window.addEventListener('online', () => {
        document.getElementById('offline-indicator').style.display = 'none';
        syncOfflineData();
    });

    window.addEventListener('offline', () => {
        document.getElementById('offline-indicator').style.display = 'block';
    });
}

// Check login status
function checkLoginStatus() {
    const user = localStorage.getItem('currentUser');
    if (user) {
        currentUser = JSON.parse(user);
        showMainScreen();
    } else {
        showLoginScreen();
    }
}

// Handle login
async function handleLogin(e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    try {
        // In production, this would call the actual API
        // For now, simulate login
        currentUser = {
            id: 1,
            name: username,
            phone: '+216 XX XXX XXX',
            vehicle: 'Camion-001'
        };

        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        showMainScreen();
    } catch (error) {
        alert('Erreur de connexion: ' + error.message);
    }
}

// Handle logout
function handleLogout() {
    localStorage.removeItem('currentUser');
    currentUser = null;
    showLoginScreen();
}

// Show screens
function showLoginScreen() {
    document.getElementById('login-screen').classList.add('active');
    document.getElementById('main-screen').classList.remove('active');
}

function showMainScreen() {
    document.getElementById('login-screen').classList.remove('active');
    document.getElementById('main-screen').classList.add('active');

    // Update profile
    document.getElementById('driver-name').textContent = currentUser.name;
    document.getElementById('profile-name').textContent = currentUser.name;
    document.getElementById('profile-phone').textContent = currentUser.phone;
    document.getElementById('profile-vehicle').textContent = currentUser.vehicle;

    // Load initial data
    loadRoutes();
    loadStats();
}

// Switch view
function switchView(viewName) {
    // Update nav
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-view="${viewName}"]`).classList.add('active');

    // Update views
    document.querySelectorAll('.view').forEach(view => {
        view.classList.remove('active');
    });
    document.getElementById(`${viewName}-view`).classList.add('active');

    // Load data based on view
    if (viewName === 'routes') {
        loadRoutes();
    } else if (viewName === 'deliveries') {
        loadDeliveries();
    } else if (viewName === 'profile') {
        loadStats();
    }
}

// Load routes
async function loadRoutes() {
    const container = document.getElementById('routes-list');
    container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';

    try {
        // Simulate API call - in production, replace with actual fetch
        const routes = [
            {
                id: 1,
                route_number: 'RT-20250118-001',
                route_date: '2025-01-18',
                total_packages: 12,
                total_distance: 45.2,
                estimated_duration: 180,
                status: 'assigned'
            }
        ];

        if (routes.length === 0) {
            container.innerHTML = '<div class="card"><p>Aucune mission disponible.</p></div>';
            return;
        }

        container.innerHTML = routes.map(route => `
            <div class="route-card" onclick="viewRouteDetail(${route.id})">
                <div class="route-card-header">
                    <div class="route-number">${route.route_number}</div>
                    <span class="badge badge-${getStatusColor(route.status)}">
                        ${getStatusText(route.status)}
                    </span>
                </div>

                <div class="route-info">
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span>${formatDate(route.route_date)}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-box"></i>
                        <span>${route.total_packages} colis</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-route"></i>
                        <span>${route.total_distance} km</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>${Math.floor(route.estimated_duration / 60)}h${route.estimated_duration % 60}m</span>
                    </div>
                </div>

                <button class="btn btn-primary btn-block">
                    <i class="fas fa-eye"></i> Voir Détails
                </button>
            </div>
        `).join('');

    } catch (error) {
        container.innerHTML = '<div class="card"><p class="text-danger">Erreur: ' + error.message + '</p></div>';
    }
}

// View route detail
async function viewRouteDetail(routeId) {
    try {
        // Simulate API call
        const route = {
            id: routeId,
            route_number: 'RT-20250118-001',
            total_packages: 12,
            total_distance: 45.2,
            stops: [
                {
                    id: 1,
                    stop_sequence: 1,
                    package_number: 'PKG-202501-0001',
                    address: '123 Rue de la République, Tunis',
                    delivery_contact: 'Mohamed Ben Ali',
                    delivery_phone: '+216 XX XXX XXX',
                    weight: 12.5,
                    status: 'pending'
                }
            ]
        };

        currentRoute = route;

        // Update modal
        document.getElementById('modal-route-title').textContent = route.route_number;

        document.getElementById('route-info').innerHTML = `
            <div class="info-item">
                <i class="fas fa-box"></i>
                <span>${route.total_packages} colis</span>
            </div>
            <div class="info-item">
                <i class="fas fa-route"></i>
                <span>${route.total_distance} km</span>
            </div>
        `;

        document.getElementById('stops-list').innerHTML = route.stops.map(stop => `
            <div class="stop-card ${stop.status === 'delivered' ? 'completed' : ''}">
                <div style="display: flex; align-items: flex-start;">
                    <span class="stop-number">${stop.stop_sequence}</span>
                    <div style="flex: 1;">
                        <strong>${stop.package_number}</strong><br>
                        <small>${stop.address}</small><br>
                        <small><i class="fas fa-user"></i> ${stop.delivery_contact}</small><br>
                        <small><i class="fas fa-phone"></i> ${stop.delivery_phone}</small>
                    </div>
                </div>
                ${stop.status !== 'delivered' ? `
                    <button class="btn btn-success btn-block mt-2" onclick="openDeliveryModal(${stop.id})">
                        <i class="fas fa-map-marker-alt"></i> Livrer
                    </button>
                ` : `
                    <div class="badge badge-success mt-2">
                        <i class="fas fa-check"></i> Livré
                    </div>
                `}
            </div>
        `).join('');

        openModal('route-detail-modal');

    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

// Open delivery modal
function openDeliveryModal(stopId) {
    const stop = currentRoute.stops.find(s => s.id === stopId);
    if (!stop) return;

    currentStop = stop;

    document.getElementById('delivery-info').innerHTML = `
        <h3>${stop.package_number}</h3>
        <p><strong>Adresse:</strong> ${stop.address}</p>
        <p><strong>Contact:</strong> ${stop.delivery_contact}</p>
        <p><strong>Téléphone:</strong> ${stop.delivery_phone}</p>
        <p><strong>Poids:</strong> ${stop.weight} kg</p>
    `;

    // Clear form
    document.getElementById('delivery-photo').value = '';
    document.getElementById('photo-preview').style.display = 'none';
    if (signaturePad) signaturePad.clear();
    document.getElementById('delivery-notes').value = '';

    closeModal('route-detail-modal');
    openModal('delivery-modal');
}

// Handle photo upload
function handlePhotoUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(event) {
        const canvas = document.getElementById('photo-preview');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = function() {
            canvas.width = 300;
            canvas.height = (img.height / img.width) * 300;
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.style.display = 'block';
        };

        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

// Mark delivery
async function markDelivery(status) {
    if (!currentStop) return;

    // Get signature
    const signatureData = signaturePad && !signaturePad.isEmpty() ? signaturePad.toDataURL() : null;

    // Get photo
    const photoCanvas = document.getElementById('photo-preview');
    const photoData = photoCanvas.style.display !== 'none' ? photoCanvas.toDataURL() : null;

    if (status === 'delivered' && (!signatureData || !photoData)) {
        alert('Veuillez prendre une photo et obtenir une signature pour confirmer la livraison.');
        return;
    }

    try {
        const data = {
            stop_id: currentStop.id,
            status: status,
            proof: photoData,
            signature: signatureData,
            notes: document.getElementById('delivery-notes').value
        };

        // In production: await fetch(`${API_URL}/updateStopStatus`, { method: 'POST', body: JSON.stringify(data) });

        // Update local state
        currentStop.status = status;

        closeModal('delivery-modal');

        alert(status === 'delivered' ? 'Livraison confirmée !' : 'Échec de livraison enregistré');

        // Reload route
        viewRouteDetail(currentRoute.id);

    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

// Load deliveries
function loadDeliveries() {
    const container = document.getElementById('deliveries-list');
    container.innerHTML = '<div class="card"><p>Toutes les livraisons en cours apparaîtront ici.</p></div>';
}

// Load stats
function loadStats() {
    document.getElementById('stat-delivered').textContent = '8';
    document.getElementById('stat-distance').textContent = '42.5';
}

// Utility functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function getStatusColor(status) {
    const colors = {
        'draft': 'secondary',
        'optimized': 'primary',
        'assigned': 'primary',
        'loading': 'warning',
        'in_progress': 'warning',
        'completed': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}

function getStatusText(status) {
    const texts = {
        'draft': 'Brouillon',
        'optimized': 'Optimisé',
        'assigned': 'Assigné',
        'loading': 'En chargement',
        'in_progress': 'En cours',
        'completed': 'Terminé',
        'cancelled': 'Annulé'
    };
    return texts[status] || status;
}

// Service Worker
function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js')
            .then(() => console.log('Service Worker registered'))
            .catch(err => console.error('Service Worker registration failed:', err));
    }
}

// Sync offline data
function syncOfflineData() {
    // Implementation for syncing offline data when back online
    console.log('Syncing offline data...');
}

// Signature Pad (Simple implementation)
class SignaturePad {
    constructor(canvas) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.drawing = false;

        this.canvas.addEventListener('mousedown', this.startDrawing.bind(this));
        this.canvas.addEventListener('mousemove', this.draw.bind(this));
        this.canvas.addEventListener('mouseup', this.stopDrawing.bind(this));
        this.canvas.addEventListener('touchstart', this.startDrawing.bind(this));
        this.canvas.addEventListener('touchmove', this.draw.bind(this));
        this.canvas.addEventListener('touchend', this.stopDrawing.bind(this));
    }

    startDrawing(e) {
        this.drawing = true;
        const pos = this.getPosition(e);
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x, pos.y);
    }

    draw(e) {
        if (!this.drawing) return;
        e.preventDefault();

        const pos = this.getPosition(e);
        this.ctx.lineTo(pos.x, pos.y);
        this.ctx.stroke();
    }

    stopDrawing() {
        this.drawing = false;
    }

    getPosition(e) {
        const rect = this.canvas.getBoundingClientRect();
        const touch = e.touches ? e.touches[0] : e;
        return {
            x: touch.clientX - rect.left,
            y: touch.clientY - rect.top
        };
    }

    clear() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }

    isEmpty() {
        const pixelBuffer = new Uint32Array(
            this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data.buffer
        );
        return !pixelBuffer.some(color => color !== 0);
    }

    toDataURL() {
        return this.canvas.toDataURL();
    }
}
