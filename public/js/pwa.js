/**
 * pwa.js — SW registration, install prompt, update detection.
 * Lee BASE desde <meta name="app-base"> → funciona en cualquier entorno.
 */

'use strict';

const BASE = document.querySelector('meta[name="app-base"]')?.content ?? '';

// ─── Service Worker ───────────────────────────────────────────────────────────

if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const reg = await navigator.serviceWorker.register(BASE + '/sw.js', {
                scope: BASE + '/',
                updateViaCache: 'none',
            });

            reg.addEventListener('updatefound', () => {
                const newWorker = reg.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdateBanner();
                    }
                });
            });

            // Forzar verificación de actualización en cada visita
            reg.update().catch(() => {});

        } catch (err) {
            console.warn('[PWA] SW registration failed:', err.message);
        }
    });

    navigator.serviceWorker.addEventListener('message', (e) => {
        if (e.data?.type === 'UPDATE_AVAILABLE') showUpdateBanner();
    });
}

// ─── Banner de actualización ──────────────────────────────────────────────────

function showUpdateBanner() {
    if (document.getElementById('pwa-update-banner')) return;

    const el = document.createElement('div');
    el.id    = 'pwa-update-banner';
    el.setAttribute('role', 'alert');
    el.style.cssText = [
        'position:fixed', 'bottom:80px', 'left:50%', 'transform:translateX(-50%)',
        'background:#0D4A77', 'color:#fff', 'padding:10px 18px', 'border-radius:999px',
        'font-size:.8rem', 'font-weight:600', 'z-index:9998',
        'box-shadow:0 4px 20px rgba(0,0,0,.25)',
        'display:flex', 'align-items:center', 'gap:12px', 'white-space:nowrap',
        'max-width:calc(100vw - 28px)',
    ].join(';');
    el.innerHTML = `
        <span>Nueva versión disponible</span>
        <button
            onclick="window.location.reload()"
            style="background:rgba(255,255,255,.2);border:none;color:#fff;padding:5px 14px;border-radius:999px;font-weight:700;cursor:pointer;font-size:.78rem;"
        >Actualizar</button>
    `;
    document.body.appendChild(el);
}

// ─── Install prompt (Android) ─────────────────────────────────────────────────

let _deferredPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    _deferredPrompt = e;

    const dismissed = localStorage.getItem('pwa_banner_dismissed');
    if (dismissed && Date.now() - parseInt(dismissed) < 7 * 86400 * 1000) return;

    // Esperar un segundo antes de mostrar para no interrumpir la carga
    setTimeout(showInstallBanner, 1200);
});

function showInstallBanner() {
    if (document.getElementById('pwa-install-banner')) return;

    const el = document.createElement('div');
    el.id    = 'pwa-install-banner';
    el.style.cssText = [
        'position:fixed', 'top:12px', 'left:50%', 'transform:translateX(-50%)',
        'background:#fff', 'border:1.5px solid #D0DDE8', 'padding:10px 14px',
        'border-radius:14px', 'font-family:Inter,sans-serif',
        'font-size:.8rem', 'font-weight:600', 'z-index:9997',
        'box-shadow:0 4px 20px rgba(13,74,119,.18)',
        'display:flex', 'align-items:center', 'gap:10px', 'color:#1C2B36',
        'max-width:calc(100vw - 24px)',
    ].join(';');
    el.innerHTML = `
        <img src="${BASE}/icons/icon-192.png" alt="" width="28" height="28"
             style="border-radius:6px;flex-shrink:0;"
             onerror="this.style.display='none'">
        <span>Instalar Yemma en tu celular</span>
        <button id="_pwa_install"
            style="background:#0D4A77;color:#fff;border:none;padding:6px 14px;border-radius:8px;font-weight:700;cursor:pointer;font-size:.78rem;flex-shrink:0;"
        >Instalar</button>
        <button id="_pwa_dismiss"
            aria-label="Cerrar"
            style="background:none;border:none;color:#9CB0BF;font-size:1.1rem;cursor:pointer;padding:2px 4px;flex-shrink:0;line-height:1;"
        >✕</button>
    `;
    document.body.appendChild(el);

    document.getElementById('_pwa_install').addEventListener('click', async () => {
        el.remove();
        if (!_deferredPrompt) return;
        _deferredPrompt.prompt();
        await _deferredPrompt.userChoice;
        _deferredPrompt = null;
    });

    document.getElementById('_pwa_dismiss').addEventListener('click', () => {
        el.remove();
        localStorage.setItem('pwa_banner_dismissed', Date.now().toString());
    });
}

window.addEventListener('appinstalled', () => {
    _deferredPrompt = null;
    document.getElementById('pwa-install-banner')?.remove();
});
