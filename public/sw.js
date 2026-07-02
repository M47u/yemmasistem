/**
 * sw.js — Service Worker de Yemma ISP
 * Auto-detecta BASE desde su propia URL → funciona en XAMPP y en producción sin cambios.
 */

'use strict';

// Derivamos el base path desde la URL del propio SW.
// XAMPP:      http://localhost/YemmaSistem/sw.js → BASE = '/YemmaSistem'
// Producción: https://dominio.com/sw.js          → BASE = ''
const BASE = new URL(self.location.href).pathname.replace(/\/sw\.js$/, '').replace(/\/$/, '');

const STATIC_CACHE = 'yemma-static-v2';
const DATA_CACHE   = 'yemma-data-v2';

const STATIC_ASSETS = [
    BASE + '/',
    BASE + '/clientes',
    BASE + '/css/app.css',
    BASE + '/css/components.css',
    BASE + '/js/app.js',
    BASE + '/js/clientes.js',
    BASE + '/js/pwa.js',
    BASE + '/offline.html',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;600;700&family=Special+Elite&display=swap',
];

// ─── Install ──────────────────────────────────────────────────────────────────

self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(STATIC_ASSETS))
            .catch(() => {})   // no bloquear la instalación si algún asset falla
    );
    self.skipWaiting();
});

// ─── Activate ─────────────────────────────────────────────────────────────────

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(k => k !== STATIC_CACHE && k !== DATA_CACHE)
                    .map(k => caches.delete(k))
            )
        )
    );
    self.clients.claim();
});

// ─── Fetch ────────────────────────────────────────────────────────────────────

self.addEventListener('fetch', (e) => {
    const url = new URL(e.request.url);

    if (e.request.method !== 'GET') return;

    // Assets estáticos → Cache First
    if (isStaticAsset(url)) {
        e.respondWith(cacheFirst(e.request));
        return;
    }

    // Navegación → Network First con fallback a página offline
    if (e.request.mode === 'navigate') {
        e.respondWith(networkFirstWithOffline(e.request));
        return;
    }

    // Datos → Network First
    if (url.pathname.startsWith(BASE + '/pagos') ||
        url.pathname.startsWith(BASE + '/clientes')) {
        e.respondWith(networkFirst(e.request));
        return;
    }
});

function isStaticAsset(url) {
    return /\.(css|js|png|jpg|svg|ico|woff2?)$/i.test(url.pathname) ||
           url.hostname === 'fonts.googleapis.com' ||
           url.hostname === 'fonts.gstatic.com';
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        const cache    = await caches.open(STATIC_CACHE);
        cache.put(request, response.clone());
        return response;
    } catch {
        return new Response('', { status: 408 });
    }
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        const cache    = await caches.open(DATA_CACHE);
        cache.put(request, response.clone());
        return response;
    } catch {
        const cached = await caches.match(request);
        return cached ?? new Response(JSON.stringify({ error: 'Sin conexión' }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' },
        });
    }
}

async function networkFirstWithOffline(request) {
    try {
        return await fetch(request);
    } catch {
        return (await caches.match(request))
            ?? (await caches.match(BASE + '/offline.html'))
            ?? new Response('<h1>Sin conexión</h1>', { headers: { 'Content-Type': 'text/html' } });
    }
}
