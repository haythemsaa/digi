/**
 * Pakiparc Driver App - Service Worker
 * Provides offline functionality and background sync
 */

const CACHE_NAME = 'pakiparc-driver-v1.0.0';
const urlsToCache = [
    '/mobile-driver-app/',
    '/mobile-driver-app/index.html',
    '/mobile-driver-app/css/app.css',
    '/mobile-driver-app/js/config.js',
    '/mobile-driver-app/js/api.js',
    '/mobile-driver-app/js/storage.js',
    '/mobile-driver-app/js/location.js',
    '/mobile-driver-app/js/app.js',
    '/mobile-driver-app/manifest.json',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js'
];

// Install event - cache resources
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
    // Skip API requests for cache
    if (event.request.url.includes('/api/')) {
        // Network first for API calls
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // Clone the response
                    const responseToCache = response.clone();

                    // Cache successful API responses
                    if (response.ok) {
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(event.request, responseToCache);
                        });
                    }

                    return response;
                })
                .catch(() => {
                    // Return cached response if network fails
                    return caches.match(event.request);
                })
        );
        return;
    }

    // Cache first for static resources
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                if (response) {
                    return response;
                }

                return fetch(event.request)
                    .then(response => {
                        // Check if valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone the response
                        const responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(cache => {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    });
            })
            .catch(() => {
                // Return offline page if available
                return caches.match('/mobile-driver-app/offline.html');
            })
    );
});

// Background sync for offline data
self.addEventListener('sync', event => {
    if (event.tag === 'sync-locations') {
        event.waitUntil(syncLocations());
    } else if (event.tag === 'sync-orders') {
        event.waitUntil(syncOrders());
    }
});

// Sync location data
async function syncLocations() {
    try {
        // Get offline location data from IndexedDB or localStorage
        const offlineData = await getOfflineData('locations');

        if (offlineData && offlineData.length > 0) {
            // Send each location to server
            for (const location of offlineData) {
                await fetch('/api/driver/location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + await getAuthToken()
                    },
                    body: JSON.stringify(location)
                });
            }

            // Clear synced data
            await clearOfflineData('locations');
        }
    } catch (error) {
        console.error('Sync locations failed:', error);
        throw error;
    }
}

// Sync order updates
async function syncOrders() {
    try {
        const offlineData = await getOfflineData('orders');

        if (offlineData && offlineData.length > 0) {
            for (const order of offlineData) {
                await fetch(`/api/driver/orders/${order.id}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + await getAuthToken()
                    },
                    body: JSON.stringify(order.data)
                });
            }

            await clearOfflineData('orders');
        }
    } catch (error) {
        console.error('Sync orders failed:', error);
        throw error;
    }
}

// Push notification event
self.addEventListener('push', event => {
    const data = event.data ? event.data.json() : {};

    const options = {
        body: data.body || 'Nouvelle notification',
        icon: '/mobile-driver-app/img/icon-192.png',
        badge: '/mobile-driver-app/img/icon-72.png',
        vibrate: [200, 100, 200],
        tag: data.tag || 'default',
        requireInteraction: true,
        data: data.data || {}
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Pakiparc Driver', options)
    );
});

// Notification click event
self.addEventListener('notificationclick', event => {
    event.notification.close();

    event.waitUntil(
        clients.openWindow(event.notification.data.url || '/mobile-driver-app/')
    );
});

// Helper functions
async function getOfflineData(type) {
    // Implementation would use IndexedDB or localStorage
    return [];
}

async function clearOfflineData(type) {
    // Implementation would clear IndexedDB or localStorage
}

async function getAuthToken() {
    // Get token from cache or storage
    return '';
}

// Message event - for communication with the app
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
