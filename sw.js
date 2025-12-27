/* NovaCloud Service Worker
   Caching strategy:
   - Precache public shell (index, about, help and static assets)
   - Runtime cache navigations using network-first (so dynamic pages get updated)
   - Cache successful navigations for offline reload later (so dashboard/profile/settings become available after first visit)
*/

const PRECACHE = 'novacloud-precache-v1';
const RUNTIME = 'novacloud-runtime';

const PRECACHE_URLS = [
  'index.php',
  'about.php',
  'help.php',
  'assets/js/main.js',
  'assets/js/language-switcher.js',
  'assets/css/style.css'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(PRECACHE)
      .then(cache => cache.addAll(PRECACHE_URLS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  const currentCaches = [PRECACHE, RUNTIME];
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.map(key => { if (!currentCaches.includes(key)) return caches.delete(key); })
    )).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const request = event.request;

  // Only handle GET requests
  if (request.method !== 'GET') return;

  // Navigation requests (HTML pages)
  if (request.mode === 'navigate') {
    // Network-first: try network, fallback to cache
    event.respondWith(
      fetch(request)
        .then(response => {
          // If response is ok, put a clone into runtime cache for offline reloads
          if (response && response.status === 200 && response.type === 'basic') {
            const copy = response.clone();
            caches.open(RUNTIME).then(cache => cache.put(request, copy));
          }
          return response;
        })
        .catch(() => caches.match(request).then(cached => cached || caches.match('index.php')))
    );
    return;
  }

  // For other same-origin requests, use cache-first then network fallback
  event.respondWith(
    caches.match(request).then(cachedResponse => {
      if (cachedResponse) return cachedResponse;
      return fetch(request).then(networkResponse => {
        // Put a copy in the runtime cache
        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
          const copy = networkResponse.clone();
          caches.open(RUNTIME).then(cache => cache.put(request, copy));
        }
        return networkResponse;
      }).catch(() => {
        // If request accepts images, fallback to a placeholder
        if (request.destination === 'image') {
          return new Response('', { status: 404, statusText: 'Not Found' });
        }
      });
    })
  );
});
