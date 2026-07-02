INSERT IGNORE INTO configuracion (clave, valor, descripcion) VALUES
    ('dia_vencimiento',  '11',  'Día del mes a partir del cual se suspende automáticamente'),
    ('moneda_simbolo',   '$',   'Símbolo de moneda local'),
    ('empresa_nombre',   'Yemma ISP', 'Nombre de la empresa'),
    ('ultima_suspension', NULL, 'Fecha de última ejecución de suspensión automática');

INSERT IGNORE INTO empresa (id, nombre) VALUES (1, 'Yemma ISP');
