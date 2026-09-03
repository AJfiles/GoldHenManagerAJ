const CACHE_NAME = 'goldhen-v3-cache';
self.addEventListener('install', (e) => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(clients.claim()));
self.addEventListener('fetch', (e) => e.respondWith(fetch(e.request)));
