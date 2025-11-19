/**
 * Pakiparc Driver App - Local Storage Manager
 */

class StorageManager {
    /**
     * Set item in localStorage
     */
    set(key, value) {
        try {
            const serialized = JSON.stringify(value);
            localStorage.setItem(key, serialized);
            return true;
        } catch (error) {
            console.error('Storage set error:', error);
            return false;
        }
    }

    /**
     * Get item from localStorage
     */
    get(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (error) {
            console.error('Storage get error:', error);
            return defaultValue;
        }
    }

    /**
     * Remove item from localStorage
     */
    remove(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.error('Storage remove error:', error);
            return false;
        }
    }

    /**
     * Clear all localStorage
     */
    clear() {
        try {
            localStorage.clear();
            return true;
        } catch (error) {
            console.error('Storage clear error:', error);
            return false;
        }
    }

    /**
     * Check if key exists
     */
    has(key) {
        return localStorage.getItem(key) !== null;
    }

    /**
     * Get all keys
     */
    keys() {
        return Object.keys(localStorage);
    }

    /**
     * Save user data
     */
    saveUser(userData) {
        return this.set(CONFIG.STORAGE_KEYS.USER_DATA, userData);
    }

    /**
     * Get user data
     */
    getUser() {
        return this.get(CONFIG.STORAGE_KEYS.USER_DATA);
    }

    /**
     * Clear user data
     */
    clearUser() {
        this.remove(CONFIG.STORAGE_KEYS.USER_DATA);
        this.remove(CONFIG.STORAGE_KEYS.AUTH_TOKEN);
    }

    /**
     * Save offline data
     */
    saveOfflineData(data) {
        const offlineData = this.get(CONFIG.STORAGE_KEYS.OFFLINE_DATA, []);
        offlineData.push({
            ...data,
            timestamp: Date.now()
        });
        return this.set(CONFIG.STORAGE_KEYS.OFFLINE_DATA, offlineData);
    }

    /**
     * Get offline data
     */
    getOfflineData() {
        return this.get(CONFIG.STORAGE_KEYS.OFFLINE_DATA, []);
    }

    /**
     * Clear offline data
     */
    clearOfflineData() {
        return this.remove(CONFIG.STORAGE_KEYS.OFFLINE_DATA);
    }

    /**
     * Save current trip
     */
    saveCurrentTrip(tripData) {
        return this.set(CONFIG.STORAGE_KEYS.CURRENT_TRIP, tripData);
    }

    /**
     * Get current trip
     */
    getCurrentTrip() {
        return this.get(CONFIG.STORAGE_KEYS.CURRENT_TRIP);
    }

    /**
     * Clear current trip
     */
    clearCurrentTrip() {
        return this.remove(CONFIG.STORAGE_KEYS.CURRENT_TRIP);
    }
}

// Create global instance
const Storage = new StorageManager();
