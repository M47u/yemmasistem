-- Contraseña por defecto: Admin1234
-- IMPORTANTE: Cambiar en el primer acceso
INSERT INTO usuarios (id, rol_id, nombre, apellido, email, password_hash, activo)
VALUES (
    1, 1, 'Administrador', 'Sistema',
    'admin@yemma.local',
    '$2y$10$yjf8Ou/dbIa4Pmcwtb2MOek.XS1kPibtFptv93XonAmxRiG4ZN8pC',
    1
)
ON DUPLICATE KEY UPDATE
    rol_id = VALUES(rol_id),
    nombre = VALUES(nombre),
    apellido = VALUES(apellido),
    email = VALUES(email),
    password_hash = VALUES(password_hash),
    activo = VALUES(activo);
