/**
 * DigiParc Driver App - Configuration
 */

const CONFIG = {
    // API Configuration
    API_BASE_URL: window.location.origin.includes('localhost') || window.location.origin.includes('127.0.0.1')
        ? 'http://localhost/digi/api'
        : window.location.origin + '/api',

    // API Endpoints
    ENDPOINTS: {
        LOGIN: '/login',
        LOGOUT: '/logout',
        PROFILE: '/driver/profile',
        ORDERS: '/driver/orders',
        ORDER_DETAIL: '/driver/orders/:id',
        UPDATE_ORDER: '/driver/orders/:id/status',
        CURRENT_TRIP: '/driver/current-trip',
        UPDATE_LOCATION: '/driver/location',
        STATS: '/driver/stats',
        NOTIFICATIONS: '/driver/notifications'
    },

    // Local Storage Keys
    STORAGE_KEYS: {
        AUTH_TOKEN: 'digiparc_driver_token',
        USER_DATA: 'digiparc_driver_user',
        REMEMBER_ME: 'digiparc_driver_remember',
        OFFLINE_DATA: 'digiparc_driver_offline',
        CURRENT_TRIP: 'digiparc_driver_current_trip'
    },

    // Geolocation
    GEO_OPTIONS: {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    },

    // Location update interval (in milliseconds)
    LOCATION_UPDATE_INTERVAL: 30000, // 30 seconds

    // Sync interval (in milliseconds)
    SYNC_INTERVAL: 60000, // 1 minute

    // Order status
    ORDER_STATUS: {
        PENDING: 'pending',
        CONFIRMED: 'confirmed',
        ASSIGNED: 'assigned',
        IN_TRANSIT: 'in_transit',
        DELIVERED: 'delivered',
        CANCELLED: 'cancelled'
    },

    // Order status translations
    ORDER_STATUS_LABELS: {
        'pending': 'En Attente',
        'confirmed': 'Confirmée',
        'assigned': 'Assignée',
        'in_transit': 'En Transit',
        'delivered': 'Livrée',
        'cancelled': 'Annulée'
    },

    // Trip stages
    TRIP_STAGES: {
        GOING_TO_PICKUP: 'going_to_pickup',
        AT_PICKUP: 'at_pickup',
        LOADING: 'loading',
        IN_TRANSIT: 'in_transit',
        AT_DELIVERY: 'at_delivery',
        DELIVERING: 'delivering',
        COMPLETED: 'completed'
    }
};

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CONFIG;
}
