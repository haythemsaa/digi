// Service Worker for DigiParc Passenger App
const CACHE_NAME = 'digiparc-passenger-v1';
const urlsToCache = [
    '/mobile-passenger-app/',
    '/mobile-passenger-app/index.html',
    '/mobile-passenger-app/css/style.css',
    '/mobile-passenger-app/js/app.js',
    '/mobile-passenger-app/manifest.json',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
];

// Install event - cache app resources
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching app shell');
                return cache.addAll(urlsToCache);
            })
            .then(() => {
                console.log('[Service Worker] Installation complete');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[Service Worker] Installation failed:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[Service Worker] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[Service Worker] Activation complete');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests except for CDN resources
    if (!event.request.url.startsWith(self.location.origin) &&
        !event.request.url.includes('cdnjs.cloudflare.com') &&
        !event.request.url.includes('unpkg.com') &&
        !event.request.url.includes('openstreetmap.org') &&
        !event.request.url.includes('nominatim.openstreetmap.org')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                // Return cached version if available
                if (cachedResponse) {
                    // For API calls, update cache in background
                    if (event.request.url.includes('/passenger_transport/')) {
                        fetch(event.request)
                            .then((networkResponse) => {
                                caches.open(CACHE_NAME)
                                    .then((cache) => {
                                        cache.put(event.request, networkResponse);
                                    });
                            })
                            .catch(() => {
                                // Network failed, keep using cached version
                            });
                    }
                    return cachedResponse;
                }

                // Not in cache, fetch from network
                return fetch(event.request)
                    .then((networkResponse) => {
                        // Cache successful GET requests
                        if (event.request.method === 'GET' && networkResponse.status === 200) {
                            const responseToCache = networkResponse.clone();
                            caches.open(CACHE_NAME)
                                .then((cache) => {
                                    cache.put(event.request, responseToCache);
                                });
                        }
                        return networkResponse;
                    })
                    .catch((error) => {
                        console.error('[Service Worker] Fetch failed:', error);

                        // Return offline page for navigation requests
                        if (event.request.mode === 'navigate') {
                            return caches.match('/mobile-passenger-app/index.html');
                        }

                        // For API requests, return error response
                        if (event.request.url.includes('/passenger_transport/')) {
                            return new Response(
                                JSON.stringify({
                                    success: false,
                                    error: 'offline',
                                    message: 'Vous êtes hors ligne. Veuillez réessayer plus tard.'
                                }),
                                {
                                    headers: { 'Content-Type': 'application/json' }
                                }
                            );
                        }

                        throw error;
                    });
            })
    );
});

// Background sync for offline bookings
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);

    if (event.tag === 'sync-bookings') {
        event.waitUntil(syncBookings());
    }
});

// Sync offline bookings
async function syncBookings() {
    try {
        const offlineBookings = await getOfflineBookings();

        if (!offlineBookings || offlineBookings.length === 0) {
            console.log('[Service Worker] No offline bookings to sync');
            return;
        }

        console.log('[Service Worker] Syncing', offlineBookings.length, 'offline bookings');

        for (const booking of offlineBookings) {
            try {
                const response = await fetch('/passenger_transport/apiCreateBooking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(booking.data)
                });

                if (response.ok) {
                    await removeOfflineBooking(booking.id);
                    console.log('[Service Worker] Synced booking:', booking.id);
                }
            } catch (error) {
                console.error('[Service Worker] Sync error for booking:', booking.id, error);
            }
        }

        // Notify clients that sync is complete
        const clients = await self.clients.matchAll();
        clients.forEach((client) => {
            client.postMessage({
                type: 'sync-complete',
                count: offlineBookings.length
            });
        });

    } catch (error) {
        console.error('[Service Worker] Sync failed:', error);
        throw error;
    }
}

// Get offline bookings from IndexedDB
async function getOfflineBookings() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('PassengerDB', 1);

        request.onerror = () => reject(request.error);

        request.onsuccess = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains('offline_bookings')) {
                resolve([]);
                return;
            }

            const transaction = db.transaction(['offline_bookings'], 'readonly');
            const store = transaction.objectStore('offline_bookings');
            const getAllRequest = store.getAll();

            getAllRequest.onsuccess = () => resolve(getAllRequest.result);
            getAllRequest.onerror = () => reject(getAllRequest.error);
        };

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('offline_bookings')) {
                db.createObjectStore('offline_bookings', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

// Remove offline booking from IndexedDB
async function removeOfflineBooking(id) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('PassengerDB', 1);

        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['offline_bookings'], 'readwrite');
            const store = transaction.objectStore('offline_bookings');
            const deleteRequest = store.delete(id);

            deleteRequest.onsuccess = () => resolve();
            deleteRequest.onerror = () => reject(deleteRequest.error);
        };

        request.onerror = () => reject(request.error);
    });
}

// Push notifications for booking updates
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push received');

    let data = {
        title: 'DigiParc Transport',
        body: 'Vous avez une nouvelle notification',
        icon: '/mobile-passenger-app/icons/icon-192x192.png',
        badge: '/mobile-passenger-app/icons/badge-72x72.png'
    };

    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            vibrate: [200, 100, 200],
            data: {
                dateOfArrival: Date.now(),
                primaryKey: 1
            },
            actions: [
                {
                    action: 'view',
                    title: 'Voir'
                },
                {
                    action: 'close',
                    title: 'Fermer'
                }
            ]
        })
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');
    event.notification.close();

    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow('/mobile-passenger-app/')
        );
    }
});

console.log('[Service Worker] Loaded successfully');
