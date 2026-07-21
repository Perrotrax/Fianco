const CACHE_NAME = 'fiancopro-v2';
const STATIC_ASSETS = [
  './',
  './dashboard.php',
  './index.php',
  './manifest.json',
  './assets/logo.png',
  './assets/icon-192.png',
  './assets/icon-512.png'
];

// Service Worker Install
self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[ServiceWorker] Caching App Shell v2');
      return cache.addAll(STATIC_ASSETS).catch(err => console.warn('Cache addAll warning:', err));
    }).then(() => self.skipWaiting())
  );
});

// Service Worker Activate
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keyList) => {
      return Promise.all(keyList.map((key) => {
        if (key !== CACHE_NAME) {
          console.log('[ServiceWorker] Removing old cache', key);
          return caches.delete(key);
        }
      }));
    }).then(() => self.clients.claim())
  );
});

// Service Worker Fetch (Network First for API, Cache First for Static Assets)
self.addEventListener('fetch', (e) => {
  const url = new URL(e.request.url);

  // Always network for API calls
  if (url.pathname.includes('/api/')) {
    e.respondWith(fetch(e.request));
    return;
  }

  // Network first fallback to cache for pages
  e.respondWith(
    fetch(e.request)
      .then((response) => {
        if (response && response.status === 200 && e.request.method === 'GET') {
          const resClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(e.request, resClone);
          });
        }
        return response;
      })
      .catch(() => caches.match(e.request))
  );
});
