<?php $user = auth(); ?>
<div class="page-wrap">
    <div class="page-header">
        <h2 class="page-title">Usuarios</h2>
        <button class="btn-primary-sm" id="btnNuevoUsuario">+ Nuevo</button>
    </div>

    <div class="user-list">
        <?php foreach ($usuarios as $u): ?>
        <div class="user-card" data-id="<?= (int)$u['id'] ?>">
            <div class="user-info">
                <div class="user-name"><?= e($u['apellido']) ?>, <?= e($u['nombre']) ?></div>
                <div class="user-meta"><?= e($u['email']) ?> · <?= e($u['rol_nombre']) ?></div>
            </div>
            <div class="user-status <?= $u['activo'] ? 'status-active' : 'status-inactive' ?>">
                <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal usuario -->
<div class="modal-overlay" id="modalUsuario">
    <div class="modal-sheet">
        <div class="modal-handle"></div>
        <div class="modal-body">
            <h2 class="modal-title" id="modalUserTitle">Nuevo usuario</h2>
            <form id="formUsuario" novalidate>
                <input type="hidden" id="userId" value="">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input class="form-input" id="uNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellido *</label>
                        <input class="form-input" id="uApellido" name="apellido" required>
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">Email *</label>
                        <input class="form-input" id="uEmail" name="email" type="email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input class="form-input" id="uPassword" name="password" type="password" placeholder="Dejar vacío para no cambiar">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rol *</label>
                        <select class="form-input" id="uRol" name="rol_id" required>
                            <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int)$rol['id'] ?>"><?= e($rol['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="btnCancelarUsuario">Cancelar</button>
                    <button type="submit" class="btn-save">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
