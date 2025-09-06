const CACHE_NAME = 'crim-fams-v2';
const urlsToCache = [
  '/',
  '/css/login.css',
  '/css/dashboard.css',
  '/js/login.js',
  '/js/dashboard.js',
  '/manifest.webmanifest'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});