<?php
$meses = ['', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
           'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

$prevMonth = $month - 1;
$prevYear  = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

$nextMonth = $month + 1;
$nextYear  = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$totalClientes  = count($clientes);
$pagaron        = count(array_filter($clientes, fn($c) => $c['pago_id']));
$noPagaron      = $totalClientes - $pagaron;
$totalCobrado   = array_sum(array_column(array_filter($clientes, fn($c) => $c['pago_id']), 'pago_importe'));
$totalPendiente = array_sum(array_map(fn($c) => $c['pago_id'] ? 0 : (float)$c['precio_mensual'], $clientes));

$isCurrentMonth = ($year == date('Y') && $month == date('m'));
?>
<div class="clientes-screen" id="clientesScreen"
     data-year="<?= $year ?>"
     data-month="<?= $month ?>">

    <!-- Header -->
    <div class="screen-header">
        <div class="header-brand">
            <svg class="brand-icon" viewBox="0 0 32 32" fill="none">
                <circle cx="16" cy="16" r="14" fill="#0D4A77" opacity=".1"/>
                <path d="M8 22 L16 10 L24 22" stroke="#0D4A77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                <path d="M11 18 L21 18" stroke="#0D4A77" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="brand-name"><?= e(APP_NAME) ?></span>
        </div>
        <?php if (can('clientes.crear')): ?>
        <button class="btn-icon" id="btnAddCliente" title="Nuevo cliente">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </button>
        <?php endif; ?>
    </div>

    <!-- Barra de mes -->
    <div class="month-bar">
        <a href="<?= url("/clientes?year=$prevYear&month=$prevMonth") ?>" class="month-arrow" id="btnPrevMonth">‹</a>
        <a href="<?= url('/clientes') ?>" class="month-label <?= $isCurrentMonth ? 'current' : '' ?>" id="monthLabel" title="Volver al mes actual">
            <?= $meses[$month] ?> <?= $year ?>
        </a>
        <a href="<?= url("/clientes?year=$nextYear&month=$nextMonth") ?>" class="month-arrow" id="btnNextMonth">›</a>
    </div>

    <!-- Barra de resumen -->
    <div class="summary-bar">
        <div class="summary-item">
            <span class="summary-num" id="sumPagaron"><?= $pagaron ?>/<?= $totalClientes ?></span>
            <span class="summary-label">Pagaron</span>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-item">
            <span class="summary-num green" id="sumCobrado"><?= money($totalCobrado) ?></span>
            <span class="summary-label">Cobrado</span>
        </div>
        <div class="summary-divider"></div>
        <div class="summary-item">
            <span class="summary-num warn" id="sumPendiente"><?= money($totalPendiente) ?></span>
            <span class="summary-label">Pendiente</span>
        </div>
    </div>

    <!-- Buscador -->
    <input
        class="search-input"
        id="searchInput"
        type="search"
        placeholder="Buscar por nombre, apellido, DNI, tel..."
        autocomplete="off"
        autocorrect="off"
    >

    <!-- Filtros -->
    <div class="filter-row">
        <button class="filter-btn active" data-filter="todos">Todos</button>
        <button class="filter-btn" data-filter="pagaron">Pagaron</button>
        <button class="filter-btn" data-filter="no">No pagaron</button>
        <button class="filter-btn" data-filter="suspendidos">Suspendidos</button>
    </div>

    <!-- Lista de clientes -->
    <div class="client-list" id="clientList">
        <?php if (empty($clientes)): ?>
        <div class="list-empty">
            <p>No hay clientes registrados.</p>
            <?php if (can('clientes.crear')): ?>
            <button class="btn-add-first" id="btnAddFirst">+ Agregar primer cliente</button>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <?php foreach ($clientes as $c): ?>
        <?php
            $pagado   = !empty($c['pago_id']);
            $estado   = $c['estado'];
            $metaInfo = [];
            if ($c['plan_nombre']) $metaInfo[] = $c['plan_nombre'];
            $metaInfo[] = money($c['precio_mensual']);
            if ($c['telefono'])  $metaInfo[] = $c['telefono'];
        ?>
        <div class="client-card <?= $estado !== 'activo' ? 'card-' . $estado : '' ?>"
             data-id="<?= (int)$c['id'] ?>"
             data-nombre="<?= e(strtolower($c['nombre'] . ' ' . $c['apellido'])) ?>"
             data-pagado="<?= $pagado ? '1' : '0' ?>"
             data-estado="<?= e($estado) ?>">
            <div class="card-info" data-id="<?= (int)$c['id'] ?>">
                <div class="card-name">
                    <?= e($c['apellido']) ?>, <?= e($c['nombre']) ?>
                    <?php if ($estado === 'suspendido'): ?><span class="badge-suspended">Susp.</span><?php endif; ?>
                </div>
                <div class="card-meta"><?= e(implode(' · ', $metaInfo)) ?></div>
            </div>
            <div class="card-actions">
                <?php if (can('pagos.registrar')): ?>
                <button
                    class="stamp <?= $pagado ? 'paid' : 'pending' ?>"
                    data-id="<?= (int)$c['id'] ?>"
                    data-year="<?= $year ?>"
                    data-month="<?= $month ?>"
                    title="<?= $pagado ? 'Pagó — clic para anular' : 'Pendiente — clic para marcar como pagado' ?>"
                >
                    <?= $pagado ? 'Pagó' : 'Pendiente' ?>
                </button>
                <?php else: ?>
                <span class="stamp-readonly <?= $pagado ? 'paid' : 'pending' ?>">
                    <?= $pagado ? 'Pagó' : 'Pendiente' ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Detalle / Editar cliente -->
<div class="modal-overlay" id="modalCliente">
    <div class="modal-sheet" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-handle"></div>
        <div class="modal-body">
            <h2 class="modal-title" id="modalTitle">Nuevo cliente</h2>
            <form id="formCliente" novalidate>
                <input type="hidden" id="clienteId" name="id" value="">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input class="form-input" id="fNombre" name="nombre" required placeholder="Juan">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido *</label>
                        <input class="form-input" id="fApellido" name="apellido" required placeholder="Pérez">
                    </div>
                    <div class="form-group">
                        <label class="form-label">DNI</label>
                        <input class="form-input" id="fDni" name="dni" placeholder="25228399" inputmode="numeric">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input class="form-input" id="fTelefono" name="telefono" placeholder="370 4123456" inputmode="tel">
                    </div>
                    <div class="form-group">
                        <label class="form-label">WhatsApp</label>
                        <input class="form-input" id="fWhatsapp" name="whatsapp" placeholder="370 4123456" inputmode="tel">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input class="form-input" id="fDireccion" name="direccion" placeholder="MZ35 C14">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Barrio</label>
                        <input class="form-input" id="fBarrio" name="barrio" placeholder="Villa del Parque">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input class="form-input" id="fEmail" name="email" type="email" placeholder="juan@mail.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plan</label>
                        <select class="form-input" id="fPlan" name="plan_id">
                            <option value="">Sin plan asignado</option>
                            <?php
                            $planes = (new \App\Models\Plan())->allActivos();
                            foreach ($planes as $plan):
                            ?>
                            <option value="<?= (int)$plan['id'] ?>"><?= e($plan['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio mensual *</label>
                        <input class="form-input" id="fPrecio" name="precio_mensual" type="number" inputmode="decimal" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Fecha de alta</label>
                        <input class="form-input" id="fFechaAlta" name="fecha_alta" type="date" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-input form-textarea" id="fObservaciones" name="observaciones" placeholder="Notas adicionales..." rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarCliente">Cancelar</button>
                    <button type="submit" class="btn-save" id="btnGuardarCliente">Guardar</button>
                </div>
                <?php if (can('clientes.eliminar')): ?>
                <button type="button" class="btn-danger" id="btnEliminarCliente" style="display:none">
                    Dar de baja
                </button>
                <?php endif; ?>
            </form>

            <!-- Historial de pagos (se muestra al editar) -->
            <div id="historialWrap" style="display:none">
                <div class="section-sep"></div>
                <div class="historial-title">Historial de pagos</div>
                <div class="historial-grid" id="historialGrid"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Registrar pago detallado -->
<div class="modal-overlay" id="modalPago">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <div class="modal-body">
            <h2 class="modal-title">Registrar pago</h2>
            <form id="formPago" novalidate>
                <input type="hidden" id="pagoClienteId" name="cliente_id">
                <input type="hidden" id="pagoYear"      name="year">
                <input type="hidden" id="pagoMonth"     name="month">
                <div class="form-group">
                    <label class="form-label">Importe *</label>
                    <input class="form-input form-input-lg" id="pagoImporte" name="importe" type="number" inputmode="decimal" step="0.01" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Método de pago</label>
                    <div class="metodos-grid" id="metodosGrid">
                        <?php
                        $pdo = \App\Core\Database::pdo();
                        $metodos = $pdo->query('SELECT * FROM metodos_pago WHERE activo = 1')->fetchAll();
                        foreach ($metodos as $i => $m):
                        ?>
                        <button type="button" class="metodo-btn <?= $i === 0 ? 'active' : '' ?>"
                                data-id="<?= (int)$m['id'] ?>">
                            <?= e($m['nombre']) ?>
                        </button>
                        <?php endforeach; ?>
                        <input type="hidden" id="pagoMetodoId" name="metodo_pago_id" value="1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Observaciones</label>
                    <input class="form-input" id="pagoObs" name="observaciones" placeholder="Opcional">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarPago">Cancelar</button>
                    <button type="submit" class="btn-save">Registrar pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('js/clientes.js') ?>"></script>
