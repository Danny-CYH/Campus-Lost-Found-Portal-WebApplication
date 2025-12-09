// sw.js - FIXED Service Worker (Session-safe version)
const CACHE_NAME = 'uum-lost-found-static-v1';
const STATIC_CACHE_NAME = 'uum-static-cache-v1';

// Only cache STATIC assets, NEVER PHP files or dynamic content
const urlsToCache = [
    './manifest.json',
    './icons/icon-192x192.png',
    './icons/icon-512x512.png',
    './css/styles.css',
    './js/theme.js',
    './offline.php'
];

// Installation - cache static assets only
self.addEventListener('install', (event) => {
    console.log('Service Worker: Installing (static assets only)...');

    event.waitUntil(
        caches.open(STATIC_CACHE_NAME)
            .then((cache) => {
                console.log('Caching static assets only...');
                return cache.addAll(urlsToCache.map(url => new Request(url, {
                    credentials: 'same-origin'
                })));
            })
            .then(() => {
                console.log('Static cache installation completed');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('Cache installation failed:', error);
                return self.skipWaiting();
            })
    );
});

// Activation - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activated');

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== STATIC_CACHE_NAME) {
                            console.log('Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - CAREFULLY handle requests
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // SKIP these requests - let browser handle them
    // 1. Skip POST/PUT/DELETE requests (login, logout, forms)
    if (event.request.method !== 'GET') {
        return;
    }

    // 2. Skip PHP files completely
    if (url.pathname.endsWith('.php')) {
        return;
    }

    // 3. Skip session-related paths
    if (url.pathname.includes('auth/') ||
        url.pathname.includes('login') ||
        url.pathname.includes('logout') ||
        url.pathname.includes('session')) {
        return;
    }

    // 4. Skip admin/dashboard areas
    if (url.pathname.includes('dashboard') ||
        url.pathname.includes('admin') ||
        url.pathname.includes('profile')) {
        return;
    }

    // 5. Only cache same-origin, static assets
    if (url.origin !== location.origin) {
        return; // Skip external resources
    }

    // Only cache static assets: CSS, JS, images, icons
    const isStaticAsset = url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|json)$/);

    if (isStaticAsset) {
        // Cache static assets with network-first strategy
        event.respondWith(
            caches.match(event.request)
                .then((cachedResponse) => {
                    // Always try network first for fresh content
                    return fetch(event.request)
                        .then((response) => {
                            // Update cache with fresh response
                            const responseToCache = response.clone();
                            caches.open(STATIC_CACHE_NAME)
                                .then(cache => {
                                    cache.put(event.request, responseToCache);
                                });
                            return response;
                        })
                        .catch(() => {
                            // If network fails, return cached version
                            return cachedResponse || new Response('Offline');
                        });
                })
        );
    } else {
        // For non-static assets, just fetch without caching
        return;
    }
});

// Listen for messages from the page
self.addEventListener('message', (event) => {
    if (event.data === 'skipWaiting') {
        self.skipWaiting();
    }
});