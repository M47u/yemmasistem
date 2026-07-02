/**
 * clientes.js — Lógica de la pantalla principal de clientes.
 * Búsqueda en tiempo real, filtros, stamp toggle, CRUD modal.
 */

'use strict';

(function () {

    // ─── Estado ────────────────────────────────────────────────────────────────
    const screen  = document.getElementById('clientesScreen');
    if (!screen) return;

    const YEAR    = parseInt(screen.dataset.year);
    const MONTH   = parseInt(screen.dataset.month);
    let currentFilter = 'todos';
    let searchQuery   = '';

    // ─── Elementos ─────────────────────────────────────────────────────────────
    const searchInput    = document.getElementById('searchInput');
    const filterBtns     = document.querySelectorAll('.filter-btn');
    const clientList     = document.getElementById('clientList');
    const modalCliente   = document.getElementById('modalCliente');
    const modalPago      = document.getElementById('modalPago');

    // ─── Búsqueda y filtros ────────────────────────────────────────────────────

    function filterCards() {
        const q       = searchQuery.toLowerCase().trim();
        const cards   = clientList.querySelectorAll('.client-card');
        let visible   = 0;

        cards.forEach(card => {
            const nombre  = card.dataset.nombre  || '';
            const pagado  = card.dataset.pagado  === '1';
            const estado  = card.dataset.estado  || 'activo';

            const matchSearch = !q || nombre.includes(q)
                || (card.querySelector('.card-meta')?.textContent.toLowerCase().includes(q) ?? false);

            const matchFilter = (() => {
                if (currentFilter === 'todos')       return true;
                if (currentFilter === 'pagaron')     return pagado;
                if (currentFilter === 'no')          return !pagado && estado === 'activo';
                if (currentFilter === 'suspendidos') return estado === 'suspendido';
                return true;
            })();

            const show = matchSearch && matchFilter;
            card.dataset.hidden = show ? 'false' : 'true';
            card.style.display  = show ? '' : 'none';
            if (show) visible++;
        });

        // Mostrar vacío si no hay resultados
        let emptyMsg = clientList.querySelector('.filter-empty');
        if (visible === 0 && cards.length > 0) {
            if (!emptyMsg) {
                emptyMsg = document.createElement('p');
                emptyMsg.className   = 'filter-empty';
                emptyMsg.style.cssText = 'text-align:center;color:var(--muted);padding:2rem 0;font-size:.88rem;';
                clientList.appendChild(emptyMsg);
            }
            emptyMsg.textContent = q
                ? `Sin resultados para "${searchQuery}"`
                : 'Sin clientes para este filtro';
        } else if (emptyMsg) {
            emptyMsg.remove();
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', e => {
            searchQuery = e.target.value;
            filterCards();
        });
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.dataset.filter;
            filterCards();
        });
    });

    // ─── Stamp: toggle de pago rápido ──────────────────────────────────────────

    clientList.addEventListener('click', async (e) => {
        const stamp = e.target.closest('.stamp:not(.stamp-readonly)');
        if (!stamp) return;

        const clienteId = parseInt(stamp.dataset.id);
        const year      = parseInt(stamp.dataset.year);
        const month     = parseInt(stamp.dataset.month);

        stamp.classList.add('loading');

        try {
            const body = new FormData();
            body.append('cliente_id', clienteId);
            body.append('year',       year);
            body.append('month',      month);

            const res = await apiFetch('/pagos/toggle', { method: 'POST', body });

            const card   = stamp.closest('.client-card');
            const pagado = res.pagado;

            // Actualizar el stamp
            stamp.textContent = pagado ? 'Pagó' : 'Pendiente';
            stamp.classList.toggle('paid',    pagado);
            stamp.classList.toggle('pending', !pagado);
            stamp.title = pagado ? 'Pagó — clic para anular' : 'Pendiente — clic para marcar como pagado';

            // Actualizar data del card
            card.dataset.pagado = pagado ? '1' : '0';

            // Actualizar barra de resumen
            updateSummary();

            showToast(pagado ? 'Pago registrado' : 'Pago anulado', pagado ? 'success' : 'info');

        } catch (err) {
            showToast(err.message || 'Error al actualizar el pago', 'error');
        } finally {
            stamp.classList.remove('loading');
        }
    });

    function updateSummary() {
        const cards = [...clientList.querySelectorAll('.client-card')];
        const total  = cards.length;
        const pagados = cards.filter(c => c.dataset.pagado === '1');

        // Las sumas de importes las actualizamos con los datos del servidor
        // Esto es una actualización optimista del contador
        document.getElementById('sumPagaron').textContent =
            pagados.length + '/' + total;

        // Redibujar filtros activos
        filterCards();
    }

    // ─── Modal de cliente (add / edit) ────────────────────────────────────────

    const formCliente       = document.getElementById('formCliente');
    const clienteIdField    = document.getElementById('clienteId');
    const modalTitle        = document.getElementById('modalTitle');
    const btnEliminar       = document.getElementById('btnEliminarCliente');
    const historialWrap     = document.getElementById('historialWrap');
    const historialGrid     = document.getElementById('historialGrid');

    function openAddForm() {
        if (!modalCliente) return;
        modalTitle.textContent = 'Nuevo cliente';
        clienteIdField.value   = '';
        formCliente.reset();
        document.getElementById('fFechaAlta').value = new Date().toISOString().slice(0, 10);
        if (btnEliminar) btnEliminar.style.display = 'none';
        if (historialWrap) historialWrap.style.display = 'none';
        openModal('modalCliente');
        document.getElementById('fNombre')?.focus();
    }

    async function openEditForm(id) {
        if (!modalCliente) return;

        try {
            const res = await apiFetch(`/clientes/${id}?year=${YEAR}&month=${MONTH}`);
            const c   = res.cliente;

            modalTitle.textContent = 'Editar cliente';
            clienteIdField.value   = c.id;

            const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
            set('fNombre',      c.nombre);
            set('fApellido',    c.apellido);
            set('fDni',         c.dni);
            set('fTelefono',    c.telefono);
            set('fWhatsapp',    c.whatsapp);
            set('fDireccion',   c.direccion);
            set('fBarrio',      c.barrio);
            set('fEmail',       c.email);
            set('fPlan',        c.plan_id);
            set('fPrecio',      c.precio_mensual);
            set('fFechaAlta',   c.fecha_alta);
            set('fObservaciones', c.observaciones);

            if (btnEliminar) btnEliminar.style.display = '';

            // Historial
            renderHistorial(res.historial ?? []);

            openModal('modalCliente');

        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    function renderHistorial(historial) {
        if (!historialGrid || !historialWrap) return;
        historialWrap.style.display = '';

        const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        historialGrid.innerHTML = '';

        // Mostrar los últimos 12 meses
        for (let i = 11; i >= 0; i--) {
            const d     = new Date(YEAR, MONTH - 1 - i, 1);
            const y     = d.getFullYear();
            const m     = d.getMonth() + 1;
            const pago  = historial.find(p => p.periodo_año == y && p.periodo_mes == m);

            const cell = document.createElement('div');
            cell.className = 'historial-mes' + (pago ? ' pagado' : '') + (d > new Date() ? ' sin-info' : '');
            cell.title     = pago
                ? `${MESES[m-1]} ${y} — Pagó $${parseFloat(pago.importe).toLocaleString('es-AR')}`
                : `${MESES[m-1]} ${y} — Sin pago`;

            cell.innerHTML = `
                <span class="historial-mes-icon">${pago ? '✓' : '·'}</span>
                <span class="historial-mes-label">${MESES[m-1]}<br>${String(y).slice(2)}</span>
            `;
            historialGrid.appendChild(cell);
        }
    }

    // Abrir modal desde card-info
    clientList.addEventListener('click', (e) => {
        const info = e.target.closest('.card-info');
        if (!info) return;
        const id = parseInt(info.dataset.id);
        if (id) openEditForm(id);
    });

    document.getElementById('btnAddCliente')?.addEventListener('click', openAddForm);
    document.getElementById('btnAddFirst')?.addEventListener('click', openAddForm);

    // Cancelar
    document.getElementById('btnCancelarCliente')?.addEventListener('click', () => closeModal('modalCliente'));

    // Submit del form
    formCliente?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id  = clienteIdField.value;
        const url = id ? `/clientes/${id}` : '/clientes';

        const btn = document.getElementById('btnGuardarCliente');
        btn.disabled = true;

        try {
            const body = new FormData(formCliente);
            const res  = await apiFetch(url, { method: 'POST', body });

            closeModal('modalCliente');

            if (id) {
                // Actualizar la card en el DOM
                const card = clientList.querySelector(`.client-card[data-id="${id}"]`);
                if (card) {
                    const c   = res.cliente;
                    card.querySelector('.card-name').childNodes[0].textContent =
                        `${c.apellido}, ${c.nombre} `;
                    const meta = [c.plan_nombre, `$${parseFloat(c.precio_mensual).toLocaleString('es-AR')}`, c.telefono].filter(Boolean);
                    card.querySelector('.card-meta').textContent = meta.join(' · ');
                    card.dataset.nombre = (c.nombre + ' ' + c.apellido).toLowerCase();
                }
                showToast('Cliente actualizado');
            } else {
                // Recargar la página para mostrar el nuevo cliente
                window.location.reload();
            }
        } catch (err) {
            showToast(err.message || 'Error al guardar', 'error');
        } finally {
            btn.disabled = false;
        }
    });

    // Dar de baja
    btnEliminar?.addEventListener('click', async () => {
        const id = clienteIdField.value;
        if (!id) return;
        if (!confirm('¿Dar de baja a este cliente? El historial se conservará.')) return;

        try {
            await apiFetch(`/clientes/${id}/baja`, { method: 'POST', body: new FormData() });
            closeModal('modalCliente');
            const card = clientList.querySelector(`.client-card[data-id="${id}"]`);
            if (card) card.remove();
            showToast('Cliente dado de baja');
        } catch (err) {
            showToast(err.message || 'Error al dar de baja', 'error');
        }
    });

    // ─── Exportar objeto público para uso desde otros módulos ─────────────────
    window.clienteModal = { openAdd: openAddForm, openEdit: openEditForm };

})();
