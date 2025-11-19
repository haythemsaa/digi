// Service Worker for Pakiparc Smart Delivery PWA
const CACHE_NAME = 'delivery-ai-v1';
const urlsToCache = [
    '/mobile-delivery-app/',
    '/mobile-delivery-app/index.html',
    '/mobile-delivery-app/css/style.css',
    '/mobile-delivery-app/js/app.js',
    '/mobile-delivery-app/manifest.json',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching app shell');
                return cache.addAll(urlsToCache);
            })
            .then(() => {
                console.log('[Service Worker] Installed successfully');
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
                console.log('[Service Worker] Activated successfully');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin) &&
        !event.request.url.includes('cdnjs.cloudflare.com')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                // Return cached version if available
                if (cachedResponse) {
                    // Update cache in background for API calls
                    if (event.request.url.includes('/smart_delivery/')) {
                        fetch(event.request)
                            .then((networkResponse) => {
                                caches.open(CACHE_NAME)
                                    .then((cache) => {
                                        cache.put(event.request, networkResponse);
                                    });
                            })
                            .catch(() => {
                                // Network failed, cached version is already returned
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
                            return caches.match('/mobile-delivery-app/index.html');
                        }

                        // For API requests, return error response
                        if (event.request.url.includes('/smart_delivery/')) {
                            return new Response(
                                JSON.stringify({
                                    success: false,
                                    error: 'offline',
                                    message: 'Vous êtes hors ligne. Les modifications seront synchronisées quand vous serez en ligne.'
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

// Background sync for offline delivery updates
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);

    if (event.tag === 'sync-deliveries') {
        event.waitUntil(syncDeliveries());
    }
});

// Sync offline delivery data
async function syncDeliveries() {
    try {
        // Get offline data from IndexedDB
        const offlineData = await getOfflineDeliveries();

        if (!offlineData || offlineData.length === 0) {
            console.log('[Service Worker] No offline data to sync');
            return;
        }

        console.log('[Service Worker] Syncing', offlineData.length, 'offline deliveries');

        // Send each offline delivery to server
        for (const delivery of offlineData) {
            try {
                const response = await fetch(delivery.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(delivery.data)
                });

                if (response.ok) {
                    // Remove from offline storage
                    await removeOfflineDelivery(delivery.id);
                    console.log('[Service Worker] Synced delivery:', delivery.id);
                } else {
                    console.error('[Service Worker] Failed to sync delivery:', delivery.id);
                }
            } catch (error) {
                console.error('[Service Worker] Sync error for delivery:', delivery.id, error);
            }
        }

        // Notify clients that sync is complete
        const clients = await self.clients.matchAll();
        clients.forEach((client) => {
            client.postMessage({
                type: 'sync-complete',
                count: offlineData.length
            });
        });

    } catch (error) {
        console.error('[Service Worker] Sync failed:', error);
        throw error;
    }
}

// Get offline deliveries from IndexedDB
async function getOfflineDeliveries() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('DeliveryAI', 1);

        request.onerror = () => reject(request.error);

        request.onsuccess = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains('offline_deliveries')) {
                resolve([]);
                return;
            }

            const transaction = db.transaction(['offline_deliveries'], 'readonly');
            const store = transaction.objectStore('offline_deliveries');
            const getAllRequest = store.getAll();

            getAllRequest.onsuccess = () => resolve(getAllRequest.result);
            getAllRequest.onerror = () => reject(getAllRequest.error);
        };

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('offline_deliveries')) {
                db.createObjectStore('offline_deliveries', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

// Remove offline delivery from IndexedDB
async function removeOfflineDelivery(id) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('DeliveryAI', 1);

        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction(['offline_deliveries'], 'readwrite');
            const store = transaction.objectStore('offline_deliveries');
            const deleteRequest = store.delete(id);

            deleteRequest.onsuccess = () => resolve();
            deleteRequest.onerror = () => reject(deleteRequest.error);
        };

        request.onerror = () => reject(request.error);
    });
}

// Push notifications for new routes
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push received');

    let data = {
        title: 'Nouvelle mission',
        body: 'Une nouvelle route de livraison vous a été assignée',
        icon: '/mobile-delivery-app/icons/icon-192x192.png',
        badge: '/mobile-delivery-app/icons/badge-72x72.png'
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
                    title: 'Voir la mission'
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
            clients.openWindow('/mobile-delivery-app/')
        );
    }
});

console.log('[Service Worker] Loaded');
