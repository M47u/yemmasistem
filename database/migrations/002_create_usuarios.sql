CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    rol_id        TINYINT UNSIGNED NOT NULL,
    nombre        VARCHAR(60)      NOT NULL,
    apellido      VARCHAR(60)      NOT NULL,
    email         VARCHAR(120)     NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    activo        TINYINT(1)       NOT NULL DEFAULT 1,
    ultimo_login  TIMESTAMP        NULL     DEFAULT NULL,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    KEY idx_usuarios_rol (rol_id),
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
