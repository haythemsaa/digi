/**
 * Pakiparc Driver App - API Client
 */

class APIClient {
    constructor() {
        this.baseURL = CONFIG.API_BASE_URL;
        this.token = Storage.get(CONFIG.STORAGE_KEYS.AUTH_TOKEN);
    }

    /**
     * Set authentication token
     */
    setToken(token) {
        this.token = token;
        Storage.set(CONFIG.STORAGE_KEYS.AUTH_TOKEN, token);
    }

    /**
     * Clear authentication token
     */
    clearToken() {
        this.token = null;
        Storage.remove(CONFIG.STORAGE_KEYS.AUTH_TOKEN);
    }

    /**
     * Get auth headers
     */
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json'
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        return headers;
    }

    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        const url = this.baseURL + endpoint;
        const config = {
            ...options,
            headers: {
                ...this.getHeaders(),
                ...options.headers
            }
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    /**
     * Login
     */
    async login(username, password) {
        const data = await this.request(CONFIG.ENDPOINTS.LOGIN, {
            method: 'POST',
            body: JSON.stringify({ username, password })
        });

        if (data.token) {
            this.setToken(data.token);
        }

        return data;
    }

    /**
     * Logout
     */
    async logout() {
        try {
            await this.request(CONFIG.ENDPOINTS.LOGOUT, {
                method: 'POST'
            });
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            this.clearToken();
        }
    }

    /**
     * Get driver profile
     */
    async getProfile() {
        return await this.request(CONFIG.ENDPOINTS.PROFILE);
    }

    /**
     * Get driver orders
     */
    async getOrders(status = null) {
        const endpoint = CONFIG.ENDPOINTS.ORDERS + (status ? `?status=${status}` : '');
        return await this.request(endpoint);
    }

    /**
     * Get order detail
     */
    async getOrderDetail(orderId) {
        const endpoint = CONFIG.ENDPOINTS.ORDER_DETAIL.replace(':id', orderId);
        return await this.request(endpoint);
    }

    /**
     * Update order status
     */
    async updateOrderStatus(orderId, status, data = {}) {
        const endpoint = CONFIG.ENDPOINTS.UPDATE_ORDER.replace(':id', orderId);
        return await this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify({ status, ...data })
        });
    }

    /**
     * Get current trip
     */
    async getCurrentTrip() {
        return await this.request(CONFIG.ENDPOINTS.CURRENT_TRIP);
    }

    /**
     * Update driver location
     */
    async updateLocation(latitude, longitude, orderId = null) {
        return await this.request(CONFIG.ENDPOINTS.UPDATE_LOCATION, {
            method: 'POST',
            body: JSON.stringify({
                latitude,
                longitude,
                order_id: orderId,
                timestamp: new Date().toISOString()
            })
        });
    }

    /**
     * Get driver statistics
     */
    async getStats() {
        return await this.request(CONFIG.ENDPOINTS.STATS);
    }

    /**
     * Get notifications
     */
    async getNotifications() {
        return await this.request(CONFIG.ENDPOINTS.NOTIFICATIONS);
    }
}

// Create global instance
const API = new APIClient();
