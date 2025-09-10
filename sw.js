// Service Worker for Eflex PWA

const CACHE_NAME = 'eflex-pwa-cache-v1';
const APP_SHELL_URLS = [
    '/',
    '/index.php',
    '/css/bootstrap.min.css',
    '/css/custom_style.css',
    '/js/bootstrap.bundle.min.js',
    '/js/main.js',
    '/includes/header.php',
    '/includes/footer.php',
    '/manifest.php' // Also cache the manifest
];

// --- INSTALL: Pre-cache the application shell ---
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Service Worker: Caching App Shell');
                // Use addAll to fetch and cache all the URLs in the app shell.
                // We use a separate cache.add for URLs that might fail without breaking the whole install
                const cachePromises = APP_SHELL_URLS.map(url => {
                    return cache.add(url).catch(err => {
                        console.warn(`Service Worker: Failed to cache ${url}`, err);
                    });
                });
                return Promise.all(cachePromises);
            })
            .then(() => {
                console.log('Service Worker: Install completed');
                return self.skipWaiting(); // Force the waiting service worker to become the active service worker.
            })
    );
});

// --- ACTIVATE: Clean up old caches ---
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cache => {
                    if (cache !== CACHE_NAME) {
                        console.log('Service Worker: Deleting old cache', cache);
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => {
            console.log('Service Worker: Activation completed');
            return self.clients.claim(); // Become the service worker for clients that are already open.
        })
    );
});

// --- FETCH: Serve from cache, fallback to network ---
self.addEventListener('fetch', event => {
    // We only want to cache GET requests.
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        caches.open(CACHE_NAME).then(cache => {
            return cache.match(event.request)
                .then(response => {
                    // If we have a match in the cache, return it.
                    if (response) {
                        // console.log(`Service Worker: Serving from cache: ${event.request.url}`);
                        return response;
                    }

                    // If no match, fetch from the network.
                    // console.log(`Service Worker: Fetching from network: ${event.request.url}`);
                    return fetch(event.request).then(networkResponse => {
                        // Optional: Cache the new resource for future use.
                        // Be careful with what you cache. We don't want to cache API responses
                        // or other dynamic content that should always be fresh.
                        // Here we're just caching everything, but a real-world app would have more complex logic.
                        if (networkResponse && networkResponse.status === 200) {
                            cache.put(event.request, networkResponse.clone());
                        }
                        return networkResponse;
                    });
                })
                .catch(error => {
                    // This is a basic offline fallback.
                    // In a real app, you might want to return a custom offline page.
                    console.warn(`Service Worker: Fetch failed for ${event.request.url}.`, error);
                });
        })
    );
});
