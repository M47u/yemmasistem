<?php $user = auth(); ?>
<nav class="app-nav">
    <a href="<?= url('/') ?>" class="nav-item <?= str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/') || str_contains($_SERVER['REQUEST_URI'] ?? '', '/dashboard') ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        <span>Panel</span>
    </a>
    <a href="<?= url('/clientes') ?>" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/clientes') ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Clientes</span>
    </a>
    <?php if (can('usuarios.ver')): ?>
    <a href="<?= url('/usuarios') ?>" class="nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/usuarios') ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Usuarios</span>
    </a>
    <?php endif; ?>
    <form method="POST" action="<?= url('/logout') ?>" style="display:contents">
        <?= csrf_field() ?>
        <button type="submit" class="nav-item nav-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Salir</span>
        </button>
    </form>
</nav>
