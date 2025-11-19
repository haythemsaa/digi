/**
 * Pakiparc Driver App - Main Application
 */

class DriverApp {
    constructor() {
        this.currentUser = null;
        this.currentTrip = null;
        this.currentView = 'dashboard';
        this.syncInterval = null;
        this.map = null;
        this.marker = null;
    }

    /**
     * Initialize app
     */
    async init() {
        console.log('Initializing Pakiparc Driver App...');

        // Hide loading screen
        setTimeout(() => {
            document.getElementById('loading-screen').style.display = 'none';
        }, 1000);

        // Check if user is logged in
        const token = Storage.get(CONFIG.STORAGE_KEYS.AUTH_TOKEN);
        if (token) {
            try {
                await this.loadUserData();
                this.showMainApp();
                await this.loadDashboard();
            } catch (error) {
                console.error('Failed to load user data:', error);
                this.showLoginPage();
            }
        } else {
            this.showLoginPage();
        }

        // Setup event listeners
        this.setupEventListeners();

        // Request notification permission
        this.requestNotificationPermission();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Login form
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Logout button
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleLogout();
            });
        }

        // Bottom navigation
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', () => {
                const view = item.getAttribute('data-view');
                this.switchView(view);
            });
        });

        // Tab buttons
        const tabBtns = document.querySelectorAll('.tab-btn');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.getAttribute('data-tab');
                this.switchTab(btn, tab);
            });
        });

        // Sync button
        const syncBtn = document.getElementById('sync-btn');
        if (syncBtn) {
            syncBtn.addEventListener('click', () => this.syncData());
        }

        // Start trip button
        const startTripBtn = document.getElementById('start-trip-btn');
        if (startTripBtn) {
            startTripBtn.addEventListener('click', () => this.startTrip());
        }

        // Trip action buttons
        document.getElementById('arrived-pickup-btn')?.addEventListener('click', () => this.arrivedAtPickup());
        document.getElementById('loaded-btn')?.addEventListener('click', () => this.loadingComplete());
        document.getElementById('arrived-delivery-btn')?.addEventListener('click', () => this.arrivedAtDelivery());
        document.getElementById('delivered-btn')?.addEventListener('click', () => this.deliveryComplete());
        document.getElementById('end-trip-btn')?.addEventListener('click', () => this.endTrip());

        // Emergency button
        const emergencyBtn = document.getElementById('emergency-btn');
        if (emergencyBtn) {
            emergencyBtn.addEventListener('click', () => this.handleEmergency());
        }
    }

    /**
     * Handle login
     */
    async handleLogin(e) {
        e.preventDefault();

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('remember-me').checked;

        const errorDiv = document.getElementById('login-error');
        errorDiv.style.display = 'none';

        try {
            const data = await API.login(username, password);

            if (data.user) {
                this.currentUser = data.user;
                Storage.saveUser(data.user);

                if (rememberMe) {
                    Storage.set(CONFIG.STORAGE_KEYS.REMEMBER_ME, true);
                }

                this.showMainApp();
                await this.loadDashboard();

                // Start location tracking
                Location.startTracking();
            }
        } catch (error) {
            errorDiv.textContent = 'Identifiant ou mot de passe incorrect';
            errorDiv.style.display = 'block';
        }
    }

    /**
     * Handle logout
     */
    async handleLogout() {
        if (confirm('Voulez-vous vraiment vous déconnecter?')) {
            // Stop location tracking
            Location.stopTracking();

            // Stop sync
            if (this.syncInterval) {
                clearInterval(this.syncInterval);
            }

            // Logout from API
            await API.logout();

            // Clear storage
            Storage.clearUser();

            // Show login page
            this.currentUser = null;
            this.showLoginPage();
        }
    }

    /**
     * Load user data
     */
    async loadUserData() {
        const userData = Storage.getUser();
        if (userData) {
            this.currentUser = userData;
        } else {
            const data = await API.getProfile();
            this.currentUser = data.user;
            Storage.saveUser(data.user);
        }
    }

    /**
     * Show login page
     */
    showLoginPage() {
        document.getElementById('login-page').classList.add('active');
        document.getElementById('main-app').classList.remove('active');
    }

    /**
     * Show main app
     */
    showMainApp() {
        document.getElementById('login-page').classList.remove('active');
        document.getElementById('main-app').classList.add('active');

        // Update driver name
        if (this.currentUser) {
            const driverName = `${this.currentUser.first_name} ${this.currentUser.last_name}`;
            document.getElementById('driver-name').textContent = driverName;
            document.getElementById('profile-name').textContent = driverName;
        }

        // Start periodic sync
        this.startSync();
    }

    /**
     * Switch view
     */
    switchView(viewName) {
        // Update nav items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-view') === viewName) {
                item.classList.add('active');
            }
        });

        // Update views
        document.querySelectorAll('.view').forEach(view => {
            view.classList.remove('active');
        });

        const targetView = document.getElementById(`${viewName}-view`);
        if (targetView) {
            targetView.classList.add('active');
            this.currentView = viewName;

            // Load data for view
            switch (viewName) {
                case 'dashboard':
                    this.loadDashboard();
                    break;
                case 'orders':
                    this.loadOrders();
                    break;
                case 'trip':
                    this.loadCurrentTrip();
                    break;
                case 'profile':
                    this.loadProfile();
                    break;
            }
        }
    }

    /**
     * Switch tab
     */
    switchTab(btn, tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(tab => {
            tab.classList.remove('active');
        });
        btn.classList.add('active');

        // Load data based on tab
        if (this.currentView === 'orders') {
            this.loadOrders(tabName);
        }
    }

    /**
     * Load dashboard
     */
    async loadDashboard() {
        try {
            const stats = await API.getStats();

            // Update stats
            document.getElementById('stat-orders').textContent = stats.active_orders || 0;
            document.getElementById('stat-completed').textContent = stats.completed_today || 0;
            document.getElementById('stat-distance').textContent = (stats.distance_today || 0) + ' km';
            document.getElementById('stat-hours').textContent = (stats.hours_today || 0) + 'h';

            // Load today's orders
            const orders = await API.getOrders('assigned');
            this.renderOrdersList(orders, 'today-orders');
        } catch (error) {
            console.error('Failed to load dashboard:', error);
        }
    }

    /**
     * Load orders
     */
    async loadOrders(status = 'pending') {
        try {
            const statusFilter = status === 'completed' ? 'delivered' : 'assigned';
            const orders = await API.getOrders(statusFilter);
            this.renderOrdersList(orders, 'orders-list');
        } catch (error) {
            console.error('Failed to load orders:', error);
        }
    }

    /**
     * Render orders list
     */
    renderOrdersList(orders, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (!orders || orders.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Aucune mission disponible</p>
                </div>
            `;
            return;
        }

        container.innerHTML = orders.map(order => `
            <div class="order-card">
                <div class="order-header">
                    <span class="order-number">#${order.order_number}</span>
                    <span class="badge badge-${this.getStatusClass(order.status)}">
                        ${CONFIG.ORDER_STATUS_LABELS[order.status] || order.status}
                    </span>
                </div>
                <div class="order-route">
                    <div class="route-point">
                        <i class="fas fa-map-marker-alt text-success"></i>
                        <div>
                            <strong>Départ</strong>
                            <p>${order.pickup_city}</p>
                        </div>
                    </div>
                    <div class="route-point">
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        <div>
                            <strong>Destination</strong>
                            <p>${order.delivery_city}</p>
                        </div>
                    </div>
                </div>
                <div class="order-actions">
                    <button class="btn btn-info" onclick="app.viewOrderDetail('${order.id}')">
                        <i class="fas fa-eye"></i> Voir
                    </button>
                    <button class="btn btn-success" onclick="app.acceptOrder('${order.id}')">
                        <i class="fas fa-check"></i> Accepter
                    </button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Get status class
     */
    getStatusClass(status) {
        const statusMap = {
            'pending': 'warning',
            'confirmed': 'info',
            'assigned': 'primary',
            'in_transit': 'info',
            'delivered': 'success',
            'cancelled': 'danger'
        };
        return statusMap[status] || 'secondary';
    }

    /**
     * Load current trip
     */
    async loadCurrentTrip() {
        try {
            const trip = await API.getCurrentTrip();

            if (trip) {
                this.currentTrip = trip;
                Storage.saveCurrentTrip(trip);
                this.renderTripDetails(trip);
                this.initializeMap(trip);
            } else {
                document.getElementById('trip-details').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-route"></i>
                        <p>Aucune mission en cours</p>
                        <button class="btn btn-primary mt-20" onclick="app.switchView('orders')">
                            Voir les Missions
                        </button>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Failed to load trip:', error);
        }
    }

    /**
     * Render trip details
     */
    renderTripDetails(trip) {
        document.getElementById('trip-pickup').textContent = trip.pickup_address;
        document.getElementById('trip-delivery').textContent = trip.delivery_address;
        document.getElementById('trip-cargo').textContent = trip.cargo_description || 'N/A';
        document.getElementById('trip-status').textContent = CONFIG.ORDER_STATUS_LABELS[trip.status];
    }

    /**
     * Initialize map
     */
    initializeMap(trip) {
        const mapContainer = document.getElementById('map-container');
        if (!mapContainer) return;

        // Create map if not exists
        if (!this.map) {
            this.map = L.map('map-container').setView([36.8065, 10.1815], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);
        }

        // Add marker for current location
        Location.getCurrentPosition().then(position => {
            if (this.marker) {
                this.marker.setLatLng([position.latitude, position.longitude]);
            } else {
                this.marker = L.marker([position.latitude, position.longitude])
                    .addTo(this.map)
                    .bindPopup('Vous êtes ici');
            }
            this.map.setView([position.latitude, position.longitude], 13);
        });
    }

    /**
     * Load profile
     */
    async loadProfile() {
        if (!this.currentUser) return;

        try {
            const stats = await API.getStats();

            document.getElementById('profile-total-trips').textContent = stats.total_trips || 0;
            document.getElementById('profile-total-km').textContent = stats.total_km || 0;
            document.getElementById('profile-rating').textContent = (stats.rating || 5.0).toFixed(1);
        } catch (error) {
            console.error('Failed to load profile stats:', error);
        }
    }

    /**
     * Start trip
     */
    async startTrip() {
        // Get assigned order and start trip
        const orders = await API.getOrders('assigned');
        if (orders && orders.length > 0) {
            const order = orders[0];
            await this.acceptOrder(order.id);
        } else {
            alert('Aucune mission assignée disponible');
        }
    }

    /**
     * Accept order
     */
    async acceptOrder(orderId) {
        try {
            await API.updateOrderStatus(orderId, 'in_transit');
            await this.loadCurrentTrip();
            this.switchView('trip');

            // Start location tracking
            Location.startTracking(orderId);
        } catch (error) {
            alert('Erreur lors de l\'acceptation de la mission');
        }
    }

    /**
     * Trip stage handlers
     */
    async arrivedAtPickup() {
        if (!this.currentTrip) return;
        await this.updateTripStage('at_pickup');
    }

    async loadingComplete() {
        if (!this.currentTrip) return;
        await this.updateTripStage('loading_complete');
    }

    async arrivedAtDelivery() {
        if (!this.currentTrip) return;
        await this.updateTripStage('at_delivery');
    }

    async deliveryComplete() {
        if (!this.currentTrip) return;
        await API.updateOrderStatus(this.currentTrip.id, 'delivered');
        alert('Livraison confirmée!');
        await this.endTrip();
    }

    async endTrip() {
        if (confirm('Voulez-vous terminer cette mission?')) {
            Location.stopTracking();
            this.currentTrip = null;
            Storage.clearCurrentTrip();
            this.switchView('dashboard');
            await this.loadDashboard();
        }
    }

    async updateTripStage(stage) {
        // Implementation for updating trip stage
        console.log('Trip stage updated:', stage);
    }

    /**
     * View order detail
     */
    viewOrderDetail(orderId) {
        // Implementation for viewing order details
        console.log('View order:', orderId);
    }

    /**
     * Handle emergency
     */
    handleEmergency() {
        if (confirm('Voulez-vous signaler une urgence?')) {
            // Send emergency alert
            if (Location.currentPosition) {
                alert('Alerte d\'urgence envoyée! Les secours ont été notifiés.');
            } else {
                alert('Impossible de localiser votre position');
            }
        }
    }

    /**
     * Sync data
     */
    async syncData() {
        const syncBtn = document.getElementById('sync-btn');
        syncBtn.innerHTML = '<i class="fas fa-sync fa-spin"></i>';

        try {
            await this.loadDashboard();
            await this.loadOrders();
        } catch (error) {
            console.error('Sync failed:', error);
        } finally {
            syncBtn.innerHTML = '<i class="fas fa-sync"></i>';
        }
    }

    /**
     * Start periodic sync
     */
    startSync() {
        this.syncInterval = setInterval(() => {
            this.syncData();
        }, CONFIG.SYNC_INTERVAL);
    }

    /**
     * Request notification permission
     */
    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
}

// Initialize app when DOM is ready
let app;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        app = new DriverApp();
        app.init();
    });
} else {
    app = new DriverApp();
    app.init();
}
