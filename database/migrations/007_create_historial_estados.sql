CREATE TABLE IF NOT EXISTS historial_estados (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cliente_id       INT UNSIGNED NOT NULL,
    estado_anterior  ENUM('activo','suspendido','baja') NOT NULL,
    estado_nuevo     ENUM('activo','suspendido','baja') NOT NULL,
    motivo           VARCHAR(200) DEFAULT NULL,
    usuario_id       INT UNSIGNED DEFAULT NULL,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_historial_cliente (cliente_id),
    CONSTRAINT fk_historial_cliente FOREIGN KEY (cliente_id) REFERENCES clientes  (id),
    CONSTRAINT fk_historial_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios  (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
