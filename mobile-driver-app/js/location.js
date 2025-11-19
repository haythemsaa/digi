/**
 * Pakiparc Driver App - Geolocation Manager
 */

class LocationManager {
    constructor() {
        this.watchId = null;
        this.currentPosition = null;
        this.isTracking = false;
        this.updateInterval = null;
    }

    /**
     * Check if geolocation is supported
     */
    isSupported() {
        return 'geolocation' in navigator;
    }

    /**
     * Get current position
     */
    async getCurrentPosition() {
        return new Promise((resolve, reject) => {
            if (!this.isSupported()) {
                reject(new Error('Geolocation not supported'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentPosition = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp
                    };
                    resolve(this.currentPosition);
                },
                (error) => {
                    reject(error);
                },
                CONFIG.GEO_OPTIONS
            );
        });
    }

    /**
     * Start watching position
     */
    startWatching(callback) {
        if (!this.isSupported()) {
            console.error('Geolocation not supported');
            return false;
        }

        if (this.watchId !== null) {
            this.stopWatching();
        }

        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                this.currentPosition = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    speed: position.coords.speed,
                    heading: position.coords.heading,
                    timestamp: position.timestamp
                };

                if (callback) {
                    callback(this.currentPosition);
                }
            },
            (error) => {
                console.error('Geolocation error:', error);
            },
            CONFIG.GEO_OPTIONS
        );

        this.isTracking = true;
        return true;
    }

    /**
     * Stop watching position
     */
    stopWatching() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
            this.isTracking = false;
        }

        if (this.updateInterval) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
    }

    /**
     * Start tracking and sending location to server
     */
    async startTracking(orderId = null) {
        // Start watching position
        this.startWatching(async (position) => {
            console.log('Position updated:', position);
        });

        // Send location to server periodically
        this.updateInterval = setInterval(async () => {
            if (this.currentPosition) {
                try {
                    await API.updateLocation(
                        this.currentPosition.latitude,
                        this.currentPosition.longitude,
                        orderId
                    );
                    console.log('Location sent to server');
                } catch (error) {
                    console.error('Failed to send location:', error);
                    // Save to offline queue if failed
                    Storage.saveOfflineData({
                        type: 'location',
                        data: {
                            latitude: this.currentPosition.latitude,
                            longitude: this.currentPosition.longitude,
                            order_id: orderId
                        }
                    });
                }
            }
        }, CONFIG.LOCATION_UPDATE_INTERVAL);

        return true;
    }

    /**
     * Stop tracking
     */
    stopTracking() {
        this.stopWatching();
    }

    /**
     * Calculate distance between two points (in km)
     */
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in km
        const dLat = this.toRad(lat2 - lat1);
        const dLon = this.toRad(lon2 - lon1);
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    /**
     * Convert degrees to radians
     */
    toRad(degrees) {
        return degrees * (Math.PI / 180);
    }

    /**
     * Check if device is near a location (within threshold in meters)
     */
    isNear(targetLat, targetLon, thresholdMeters = 100) {
        if (!this.currentPosition) {
            return false;
        }

        const distance = this.calculateDistance(
            this.currentPosition.latitude,
            this.currentPosition.longitude,
            targetLat,
            targetLon
        );

        return (distance * 1000) <= thresholdMeters;
    }

    /**
     * Get formatted coordinates
     */
    getFormattedCoordinates() {
        if (!this.currentPosition) {
            return 'Position non disponible';
        }

        return `${this.currentPosition.latitude.toFixed(6)}, ${this.currentPosition.longitude.toFixed(6)}`;
    }
}

// Create global instance
const Location = new LocationManager();
