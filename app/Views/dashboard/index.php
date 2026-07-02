<?php
$user = auth();
$m    = $metricas;
?>
<div class="dashboard">
    <div class="page-header">
        <div>
            <h2 class="page-title">Panel</h2>
            <p class="page-sub">Hola, <?= e($user['nombre']) ?></p>
        </div>
        <a href="<?= url('/clientes') ?>" class="btn-primary-sm">Ver clientes →</a>
    </div>

    <!-- Métricas de clientes -->
    <div class="metrics-grid">
        <div class="metric-card metric-active">
            <div class="metric-num"><?= e($m['clientes_activos']) ?></div>
            <div class="metric-label">Activos</div>
        </div>
        <div class="metric-card metric-suspended">
            <div class="metric-num"><?= e($m['clientes_suspendidos']) ?></div>
            <div class="metric-label">Suspendidos</div>
        </div>
        <div class="metric-card metric-baja">
            <div class="metric-num"><?= e($m['clientes_baja']) ?></div>
            <div class="metric-label">Baja</div>
        </div>
    </div>

    <!-- Métricas financieras -->
    <div class="section-title">
        <span><?= periodLabel($m['year'], $m['month']) ?></span>
    </div>
    <div class="finance-grid">
        <div class="finance-card">
            <div class="finance-label">Cobrado hoy</div>
            <div class="finance-amount finance-green"><?= money($m['ingresos_hoy']) ?></div>
            <div class="finance-sub"><?= e($m['pagos_hoy']) ?> pago<?= $m['pagos_hoy'] != 1 ? 's' : '' ?></div>
        </div>
        <div class="finance-card">
            <div class="finance-label">Cobrado en el mes</div>
            <div class="finance-amount finance-navy"><?= money($m['ingresos_mes']) ?></div>
            <div class="finance-sub"><?= e($m['pagos_mes']) ?> de <?= e($m['total_clientes']) ?> clientes</div>
        </div>
    </div>

    <!-- Acceso rápido -->
    <div class="quick-actions">
        <a href="<?= url('/clientes') ?>" class="quick-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Gestionar clientes
        </a>
        <?php if (can('clientes.crear')): ?>
        <button class="quick-btn" id="btnNuevoCliente" onclick="window.clienteModal && window.clienteModal.openAdd()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo cliente
        </button>
        <?php endif; ?>
    </div>
</div>
