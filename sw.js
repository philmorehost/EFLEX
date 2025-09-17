// A safe, non-caching service worker to prevent the issues described by the user.
// This service worker can be enhanced with caching strategies later in a separate, focused task.

self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing (Safe Version)...');
  // By not caching anything here and calling skipWaiting, we ensure the new service worker
  // activates quickly without holding onto old, potentially problematic caches.
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activating (Safe Version)...');
  // Claiming clients ensures the new service worker takes control of open pages immediately.
  event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
  // This is a "pass-through" or "network-only" fetch listener.
  // It does not intercept the request. It simply forwards it to the network.
  // This is the safest possible strategy and avoids all caching-related problems,
  // such as the session/logout issues the user was concerned about.
  event.respondWith(fetch(event.request));
});
