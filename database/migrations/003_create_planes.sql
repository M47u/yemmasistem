CREATE TABLE IF NOT EXISTS planes (
    id           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(60)       NOT NULL,
    velocidad_mb SMALLINT UNSIGNED DEFAULT NULL,
    precio_base  DECIMAL(10,2)     NOT NULL,
    activo       TINYINT(1)        NOT NULL DEFAULT 1,
    created_at   TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
