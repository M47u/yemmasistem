/**
 * app.js — Funciones globales compartidas
 */

'use strict';

/**
 * Fetch con CSRF automático y manejo de errores centralizado.
 */
async function apiFetch(url, options = {}) {
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token':     CSRF_TOKEN,
        ...(options.headers || {}),
    };

    const config = { ...options, headers };

    const response = await fetch(APP_URL + url, config);
    const data     = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.error || `Error ${response.status}`);
    }

    return data;
}

/**
 * Muestra un toast flotante.
 */
function showToast(message, type = 'success') {
    const existing = document.querySelector('.flash-toast');
    if (existing) existing.remove();

    const t = document.createElement('div');
    t.className = `flash-toast flash-${type}`;
    t.textContent = message;
    document.body.appendChild(t);

    setTimeout(() => t.classList.add('flash-hide'), 2800);
    setTimeout(() => t.remove(), 3200);
}

/**
 * Abre un modal overlay.
 */
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('show');
}

/**
 * Cierra un modal overlay.
 */
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('show');
}

// Cierra el modal al hacer click en el overlay (fuera del sheet)
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});
