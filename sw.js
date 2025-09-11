// =================================================================
// CBT PLATFORM SERVICE WORKER
// =================================================================
// Version 1.0

const CACHE_NAME_STATIC = 'cbt-static-v1';
const CACHE_NAME_DYNAMIC = 'cbt-dynamic-v1';

// App Shell: The static assets that are fundamental to the application's UI.
const APP_SHELL_URLS = [
    '/index.php',
    '/manifest.json',
    // Note: CSS, JS, and image assets will be added here in a later phase.
    // For now, this is just a placeholder.
];

// --- INSTALL Event ---
// Fired when the service worker is first installed.
self.addEventListener('install', event => {
    console.log('[Service Worker] Installing...');
    // Pre-cache the application shell.
    event.waitUntil(
        caches.open(CACHE_NAME_STATIC).then(cache => {
            console.log('[Service Worker] Pre-caching App Shell...');
            return cache.addAll(APP_SHELL_URLS);
        })
    );
    // Force the waiting service worker to become the active service worker.
    self.skipWaiting();
});

// --- ACTIVATE Event ---
// Fired when the service worker is activated.
self.addEventListener('activate', event => {
    console.log('[Service Worker] Activating...');
    // Clean up old caches that are not in use anymore.
    event.waitUntil(
        caches.keys().then(keyList => {
            return Promise.all(keyList.map(key => {
                if (key !== CACHE_NAME_STATIC && key !== CACHE_NAME_DYNAMIC) {
                    console.log('[Service Worker] Removing old cache:', key);
                    return caches.delete(key);
                }
            }));
        })
    );
    // Take control of all open clients immediately.
    return self.clients.claim();
});

// --- FETCH Event ---
// Fired for every network request made by the page.
self.addEventListener('fetch', event => {
    // For now, we will implement a simple network-first strategy.
    // More complex strategies will be implemented later as per the project plan.
    event.respondWith(
        fetch(event.request).catch(() => {
            // This is a basic offline fallback.
            // In a later phase, we can return a custom offline page.
            // For now, it will just fail if the network request fails,
            // unless the resource is in the cache from the 'install' step.
            return caches.match(event.request);
        })
    );
});
