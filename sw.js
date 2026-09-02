/**
 * ====================================================================
 * GOLDHEN MANAGER V3.0 🚀 (PS4) - SERVICE WORKER (PWA)
 * DEVELOPED By SeBaS - RUTA: sw.js
 * ====================================================================
 */
const CACHE_NAME = 'goldhen-v3-cache';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    event.respondWith(fetch(event.request).catch(() => {
        return new Response('Offline');
    }));
});
});
ar peticiones (dejamos que el servidor local de Termux haga el trabajo)
self.addEventListener('fetch', (event) => {
    event.respondWith(fetch(event.request));
});
