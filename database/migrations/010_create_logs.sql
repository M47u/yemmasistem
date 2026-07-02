CREATE TABLE IF NOT EXISTS logs (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED    DEFAULT NULL,
    accion     VARCHAR(60)     NOT NULL,
    entidad    VARCHAR(40)     DEFAULT NULL,
    entidad_id INT UNSIGNED    DEFAULT NULL,
    datos_json JSON            DEFAULT NULL,
    ip         VARCHAR(45)     DEFAULT NULL,
    user_agent VARCHAR(255)    DEFAULT NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_logs_usuario (usuario_id),
    KEY idx_logs_created (created_at),
    CONSTRAINT fk_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
