// =================================================================
// CBT PLATFORM SERVICE WORKER
// =================================================================
// Version 1.1

const CACHE_NAME_STATIC = 'cbt-static-v2'; // Updated version
const CACHE_NAME_DYNAMIC = 'cbt-dynamic-v2'; // Updated version

// App Shell: The static assets that are fundamental to the application's UI.
const APP_SHELL_URLS = [
    '/',
    '/index.php',
    '/manifest.json',
    '/assets/css/style.css',
    '/assets/js/app.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'
];

// --- INSTALL Event ---
self.addEventListener('install', event => {
    console.log('[Service Worker] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME_STATIC).then(cache => {
            console.log('[Service Worker] Pre-caching App Shell...');
            return cache.addAll(APP_SHELL_URLS);
        })
    );
    self.skipWaiting();
});

// --- ACTIVATE Event ---
self.addEventListener('activate', event => {
    console.log('[Service Worker] Activating...');
    event.waitUntil(
        caches.keys().then(keyList => {
            return Promise.all(keyList.map(key => {
                // Delete old caches
                if (key !== CACHE_NAME_STATIC && key !== CACHE_NAME_DYNAMIC) {
                    console.log('[Service Worker] Removing old cache:', key);
                    return caches.delete(key);
                }
            }));
        })
    );
    return self.clients.claim();
});

// --- FETCH Event ---
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Strategy 1: Cache First for static assets (CSS, JS, Fonts, Images)
    if (APP_SHELL_URLS.includes(url.pathname) || url.origin.includes('cdn.jsdelivr.net')) {
        event.respondWith(
            caches.match(event.request).then(response => {
                return response || fetch(event.request);
            })
        );
    }
    // Strategy 2: Network First for dynamic content (HTML pages, API calls)
    else {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // If the request is successful, cache it for offline use
                    return caches.open(CACHE_NAME_DYNAMIC).then(cache => {
                        // We only cache GET requests
                        if (event.request.method === 'GET') {
                            cache.put(event.request.url, response.clone());
                        }
                        return response;
                    });
                })
                .catch(() => {
                    // If network fails, try to get it from the cache
                    return caches.match(event.request).then(response => {
                        if (response) {
                            return response;
                        }
                        // Optional: return a custom offline page if nothing is cached
                        // return caches.match('/offline.html');
                    });
                })
        );
    }
});
