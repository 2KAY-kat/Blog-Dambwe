const CACHE_NAME = 'blog-dambwe-cache-v1';
const urlsToCache = [
    '/Blog-Dambwe/',
    '/Blog-Dambwe/index.php',
    '/Blog-Dambwe/blog.php',
    '/Blog-Dambwe/about.php',
    '/Blog-Dambwe/css/style.css',
    '/Blog-Dambwe/css/interactions.css',
    '/Blog-Dambwe/css/skeleton.css',
    '/Blog-Dambwe/scripts/main.js',
    '/Blog-Dambwe/js/notifications.js',
    '/Blog-Dambwe/js/skeleton-loader.js',
    '/Blog-Dambwe/partials/favicon.png',
    '/Blog-Dambwe/partials/favicon.ico',
    '/Blog-Dambwe/partials/icon-192.png',
    '/Blog-Dambwe/partials/icon-512.png'
];

// Install Service Worker
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(urlsToCache);
        })
    );
});

// Fetch from Cache
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});

// Update Service Worker
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

