# ARQUITECTURA — Sistema de Gestión ISP "Yemma"

> Documento de arquitectura definitivo. Toda decisión de diseño debe reflejarse aquí antes de escribir código.

---

## 1. VISIÓN GENERAL

Sistema de gestión para empresa proveedora de Internet (ISP).
Desarrollado con PHP 8.3 puro + MySQL + Bootstrap 5.3.
Ejecuta en XAMPP local desde el día 1; preparado para migración a producción sin cambios estructurales.
Es una PWA instalable en Android, Mobile First.

**Principio rector:** La experiencia del sistema actual (HTML de referencia) es el contrato UX.
El usuario debe sentir que usa una versión moderna del mismo sistema — no aprender uno nuevo.

---

## 2. ANÁLISIS DEL SISTEMA DE REFERENCIA

Del análisis del HTML existente se extraen los patrones que **DEBEN conservarse**:

| Elemento | Comportamiento actual | Nuevo sistema |
|---|---|---|
| Lista de clientes | Cards con nombre, plan, precio, teléfono | Mismo layout, misma información |
| Stamp de pago | Botón "Pagó / Pendiente" por mes | Mismo mecanismo, datos en MySQL |
| Navegación de meses | ‹ Mes Año › | Idéntico |
| Barra de resumen | Pagaron X/Total · Cobrado · Pendiente | Idéntico + datos reales de BD |
| Búsqueda | Input único en tiempo real | Mismo, con más campos (apellido, DNI, tel) |
| Filtros | Todos / Pagaron / No pagaron | Idéntico |
| Modal de cliente | Bottom sheet (desliza desde abajo) | Mismo patrón, más campos |
| Paleta de colores | Navy, grises azulados, verde pagado, naranja pendiente | Conservada y refinada |
| Tipografía | Special Elite (títulos) + IBM Plex Mono (datos) + Inter (UI) | Conservada |

**Lo que el nuevo sistema agrega sin romper la UX:**
- Datos reales en servidor (no localStorage)
- Historial completo de pagos por período
- Suspensión automática día 11
- Múltiples usuarios con roles
- Más campos por cliente
- PWA instalable

---

## 3. TECNOLOGÍAS

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | PHP puro | 8.3 |
| Base de datos | MySQL | 8.0+ |
| Frontend UI | Bootstrap | 5.3 |
| Frontend JS | Vanilla JavaScript | ES2022 |
| Estilos | CSS puro | - |
| Base de datos ORM | PDO | nativo PHP |
| Servidor | Apache (XAMPP) | 2.4+ |
| PWA | Web App Manifest + Service Worker | - |

---

## 4. ESTRUCTURA DE CARPETAS

```
/YemmaSistem
│
├── /app
│   ├── /Controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ClienteController.php
│   │   ├── PagoController.php
│   │   ├── PlanController.php
│   │   ├── UsuarioController.php
│   │   └── ConfigController.php
│   │
│   ├── /Models
│   │   ├── BaseModel.php          ← CRUD genérico sobre PDO
│   │   ├── Usuario.php
│   │   ├── Cliente.php
│   │   ├── Pago.php
│   │   ├── Plan.php
│   │   ├── HistorialEstado.php
│   │   └── LogActividad.php
│   │
│   ├── /Views
│   │   ├── /layouts
│   │   │   ├── app.php            ← layout autenticado (header + nav + content + footer)
│   │   │   └── auth.php           ← layout login
│   │   ├── /auth
│   │   │   └── login.php
│   │   ├── /dashboard
│   │   │   └── index.php
│   │   ├── /clientes
│   │   │   ├── index.php          ← lista principal (réplica funcional del HTML de referencia)
│   │   │   └── _card.php          ← partial: una card de cliente
│   │   ├── /pagos
│   │   │   └── historial.php
│   │   ├── /usuarios
│   │   │   ├── index.php
│   │   │   └── form.php
│   │   ├── /errors
│   │   │   ├── 403.php
│   │   │   ├── 404.php
│   │   │   └── 500.php
│   │   └── /partials
│   │       ├── head.php           ← meta, css, manifest
│   │       ├── nav.php            ← barra de navegación
│   │       └── flash.php          ← mensajes flash
│   │
│   ├── /Core
│   │   ├── Application.php        ← bootstrap del framework, inyecta dependencias
│   │   ├── Router.php             ← enruta URLs a Controller@method
│   │   ├── Controller.php         ← clase base con render(), redirect(), json()
│   │   ├── Model.php              ← clase base con conexión PDO
│   │   ├── View.php               ← renderiza templates PHP con variables
│   │   ├── Request.php            ← encapsula $_GET, $_POST, $_FILES, headers
│   │   ├── Response.php           ← helpers para respuestas HTTP y JSON
│   │   ├── Session.php            ← wrapper de sesiones PHP
│   │   ├── Auth.php               ← autenticación: login, logout, usuario actual
│   │   ├── Database.php           ← singleton PDO con lazy-connect
│   │   ├── CSRF.php               ← genera y valida tokens CSRF
│   │   └── Validator.php          ← reglas de validación reutilizables
│   │
│   ├── /Middleware
│   │   ├── AuthMiddleware.php     ← redirige a /login si no autenticado
│   │   ├── RoleMiddleware.php     ← verifica que el rol tenga permiso para la ruta
│   │   └── CSRFMiddleware.php     ← valida token CSRF en POST/PUT/DELETE
│   │
│   ├── /Helpers
│   │   └── functions.php          ← funciones globales: money(), dateEs(), sanitize()
│   │
│   └── /Services
│       ├── SuspensionService.php  ← lógica de suspensión automática día 11
│       ├── PagoService.php        ← registrar pago + reactivar cliente
│       └── DashboardService.php   ← cálculo de métricas para el dashboard
│
├── /config
│   ├── app.php                    ← nombre app, zona horaria, entorno
│   ├── database.php               ← credenciales DB (lee de .env o constantes)
│   └── permissions.php            ← matriz de permisos por rol
│
├── /database
│   ├── /migrations
│   │   ├── 001_create_roles.sql
│   │   ├── 002_create_usuarios.sql
│   │   ├── 003_create_planes.sql
│   │   ├── 004_create_clientes.sql
│   │   ├── 005_create_pagos.sql
│   │   ├── 006_create_historial_estados.sql
│   │   ├── 007_create_metodos_pago.sql
│   │   ├── 008_create_configuracion.sql
│   │   ├── 009_create_empresa.sql
│   │   └── 010_create_logs.sql
│   └── /seeds
│       ├── 01_roles.sql
│       ├── 02_admin_default.sql
│       ├── 03_planes.sql
│       └── 04_metodos_pago.sql
│
├── /public                        ← DocumentRoot de Apache
│   ├── /css
│   │   ├── app.css                ← variables + estilos globales
│   │   └── components.css         ← cards, stamps, modales, etc.
│   ├── /js
│   │   ├── app.js                 ← inicialización global
│   │   ├── clientes.js            ← búsqueda en tiempo real, stamp toggle
│   │   ├── pwa.js                 ← beforeinstallprompt, update detection
│   │   └── sw.js                  ← Service Worker
│   ├── /img
│   ├── /icons                     ← iconos PWA (192x192, 512x512, maskable)
│   ├── /uploads                   ← comprobantes de pago (futura implementación)
│   ├── index.php                  ← entry point: instancia Application, despacha router
│   ├── manifest.json              ← PWA manifest
│   └── offline.html               ← página offline del Service Worker
│
├── /storage
│   ├── /logs                      ← logs de errores PHP (no expuesto al web)
│   └── /cache                     ← cache simple de respuestas (opcional)
│
├── /routes
│   └── web.php                    ← definición de todas las rutas
│
├── .htaccess                      ← redirige todo a /public
├── .env.example                   ← plantilla de variables de entorno
├── .gitignore
└── ARQUITECTURA.md                ← este archivo
```

---

## 5. DISEÑO DE LA BASE DE DATOS

### 5.1 Diagrama de relaciones

```
roles ──< usuarios >── sesiones
          │
          │
planes ──< clientes >── historial_estados
              │
              ├──< pagos >── metodos_pago
              │         └── usuarios (registrado_por)
              │
              └── logs
```

### 5.2 Tablas detalladas

#### `roles`
```sql
id          TINYINT UNSIGNED PK AUTO_INCREMENT
nombre      VARCHAR(30) NOT NULL UNIQUE   -- 'admin','operador','cajero','tecnico'
descripcion VARCHAR(100)
created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

#### `usuarios`
```sql
id              INT UNSIGNED PK AUTO_INCREMENT
rol_id          TINYINT UNSIGNED FK → roles.id
nombre          VARCHAR(60) NOT NULL
apellido        VARCHAR(60) NOT NULL
email           VARCHAR(120) NOT NULL UNIQUE
password_hash   VARCHAR(255) NOT NULL
activo          TINYINT(1) DEFAULT 1
ultimo_login    TIMESTAMP NULL
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

#### `planes`
```sql
id           SMALLINT UNSIGNED PK AUTO_INCREMENT
nombre       VARCHAR(60) NOT NULL        -- '20 Mbps', '50 Mbps Fibra'
velocidad_mb SMALLINT UNSIGNED           -- 20, 50, 100
precio_base  DECIMAL(10,2) NOT NULL
activo       TINYINT(1) DEFAULT 1
created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

#### `clientes`
```sql
id              INT UNSIGNED PK AUTO_INCREMENT
numero_interno  SMALLINT UNSIGNED UNIQUE NOT NULL   -- nro. visible al operador
nombre          VARCHAR(80) NOT NULL
apellido        VARCHAR(80) NOT NULL
dni             VARCHAR(15)
direccion       VARCHAR(150)
barrio          VARCHAR(80)
telefono        VARCHAR(20)
whatsapp        VARCHAR(20)
email           VARCHAR(120)
plan_id         SMALLINT UNSIGNED FK → planes.id
precio_mensual  DECIMAL(10,2) NOT NULL              -- puede diferir del plan base
fecha_alta      DATE NOT NULL
estado          ENUM('activo','suspendido','baja') DEFAULT 'activo'
observaciones   TEXT
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at      TIMESTAMP NULL                      -- soft delete
INDEX idx_estado (estado)
INDEX idx_numero_interno (numero_interno)
INDEX idx_dni (dni)
INDEX idx_apellido (apellido)
```

#### `metodos_pago`
```sql
id      TINYINT UNSIGNED PK AUTO_INCREMENT
nombre  VARCHAR(40) NOT NULL    -- 'Efectivo','Transferencia','Mercado Pago'
activo  TINYINT(1) DEFAULT 1
```

#### `pagos`
```sql
id               INT UNSIGNED PK AUTO_INCREMENT
cliente_id       INT UNSIGNED FK → clientes.id
periodo_año      SMALLINT UNSIGNED NOT NULL    -- 2025
periodo_mes      TINYINT UNSIGNED NOT NULL     -- 1..12
fecha_pago       DATE NOT NULL
importe          DECIMAL(10,2) NOT NULL
metodo_pago_id   TINYINT UNSIGNED FK → metodos_pago.id
usuario_id       INT UNSIGNED FK → usuarios.id  -- quien registró
observaciones    TEXT
comprobante_path VARCHAR(255) NULL             -- futura impl
created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
UNIQUE KEY uq_pago_periodo (cliente_id, periodo_año, periodo_mes)
INDEX idx_cliente (cliente_id)
INDEX idx_periodo (periodo_año, periodo_mes)
INDEX idx_fecha (fecha_pago)
```

**Nota crítica:** el UNIQUE en `(cliente_id, periodo_año, periodo_mes)` garantiza que no
se registre el mismo período dos veces. Para anular un pago se registra en `historial_estados`,
nunca se borra el pago.

#### `historial_estados`
```sql
id               INT UNSIGNED PK AUTO_INCREMENT
cliente_id       INT UNSIGNED FK → clientes.id
estado_anterior  ENUM('activo','suspendido','baja')
estado_nuevo     ENUM('activo','suspendido','baja')
motivo           VARCHAR(200)           -- 'pago registrado', 'vencimiento día 11', 'baja manual'
usuario_id       INT UNSIGNED FK → usuarios.id NULL  -- NULL si fue automático
created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
INDEX idx_cliente (cliente_id)
```

#### `empresa`
```sql
id          TINYINT UNSIGNED PK AUTO_INCREMENT
nombre      VARCHAR(100) NOT NULL
logo_path   VARCHAR(255)
telefono    VARCHAR(20)
email       VARCHAR(120)
direccion   VARCHAR(200)
updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

#### `configuracion`
```sql
clave       VARCHAR(60) PK
valor       TEXT
descripcion VARCHAR(200)
updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```
Claves importantes: `dia_vencimiento` (default: 11), `moneda_simbolo` (default: '$').

#### `logs`
```sql
id               BIGINT UNSIGNED PK AUTO_INCREMENT
usuario_id       INT UNSIGNED NULL FK → usuarios.id
accion           VARCHAR(60) NOT NULL   -- 'login','registrar_pago','editar_cliente'
entidad          VARCHAR(40)            -- 'Cliente','Pago'
entidad_id       INT UNSIGNED NULL
datos_json       JSON NULL              -- snapshot antes/después del cambio
ip               VARCHAR(45)
user_agent       VARCHAR(255)
created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
INDEX idx_usuario (usuario_id)
INDEX idx_created (created_at)
```

---

## 6. FLUJO DEL SISTEMA

### 6.1 Flujo de request HTTP

```
Browser → Apache (.htaccess) → /public/index.php
    → Application::boot()
        → Database::connect()        [singleton PDO]
        → Session::start()
        → Router::dispatch()
            → CSRFMiddleware (si POST/PUT/DELETE)
            → AuthMiddleware (si ruta protegida)
            → RoleMiddleware (si ruta con permisos)
            → Controller@method()
                → Model → PDO → MySQL
                → View::render()
                → Response (HTML o JSON)
```

### 6.2 Flujo principal de trabajo (operador)

```
1. Abrir app (PWA instalada)
2. Login → dashboard
3. Pantalla principal: lista de clientes del mes actual
4. Búsqueda por nombre/apellido/nro/DNI/tel → filtrado inmediato (JS)
5. Tap en stamp "Pendiente" → POST AJAX /pagos/toggle → MySQL → respuesta JSON
6. Card actualiza estado visual sin recarga
7. Tap en nombre del cliente → bottom sheet con detalle + historial de 12 meses
8. Opcional: registrar pago con método, importe, observaciones
9. Volver automáticamente a la lista
```

**El objetivo de 20 segundos es viable:** los pasos 3-6 son 3 toques.

### 6.3 Flujo de suspensión automática

```
Diariamente a las 00:01 (cron en producción / trigger en XAMPP):
    SuspensionService::run()
        → Si hoy es >= día 11 del mes
            → Buscar clientes activos sin pago del período actual
            → UPDATE clientes SET estado = 'suspendido'
            → INSERT historial_estados (motivo: 'Suspensión automática por vencimiento')

Cuando se registra un pago:
    PagoService::registrar()
        → INSERT pagos
        → Si cliente.estado = 'suspendido'
            → UPDATE clientes SET estado = 'activo'
            → INSERT historial_estados (motivo: 'Reactivación por pago')
```

**En XAMPP** (sin cron real): la suspensión se verifica al cargar la lista de clientes.
Se ejecuta `SuspensionService::run()` una vez por sesión, guardando la última ejecución
en `configuracion.ultima_suspension`.

---

## 7. ESTRATEGIA MVC

### 7.1 Router

```
GET  /                     → DashboardController@index
GET  /login                → AuthController@loginForm
POST /login                → AuthController@login
POST /logout               → AuthController@logout

GET  /clientes             → ClienteController@index        [lista + mes]
GET  /clientes/nuevo       → ClienteController@createForm
POST /clientes             → ClienteController@store
GET  /clientes/{id}        → ClienteController@show         [bottom sheet / detalle]
POST /clientes/{id}        → ClienteController@update
POST /clientes/{id}/baja   → ClienteController@softDelete

POST /pagos                → PagoController@store           [AJAX]
POST /pagos/toggle         → PagoController@toggle          [AJAX: pago rápido]
GET  /pagos/historial/{id} → PagoController@historial       [AJAX: 12 meses]

GET  /usuarios             → UsuarioController@index        [solo admin]
POST /usuarios             → UsuarioController@store
POST /usuarios/{id}        → UsuarioController@update

GET  /config               → ConfigController@index         [solo admin]
POST /config               → ConfigController@update
```

### 7.2 Controllers

Extienden `Core\Controller`. Responsabilidades:
- Recibir Request
- Validar input con Validator
- Llamar a Model o Service
- Renderizar View o retornar JSON

Nunca contienen lógica de negocio. Esa va en Services o Models.

### 7.3 Models

Extienden `Core\Model`. Cada model:
- Tiene métodos específicos del dominio: `findByEstado()`, `buscador()`, `porPeriodo()`
- Usa PDO con prepared statements siempre
- Devuelve arrays asociativos o null, nunca objetos genéricos
- No conoce HTTP ni Views

### 7.4 Views

Archivos PHP puros. Reciben variables via `extract()`.
El layout `app.php` define la estructura común e incluye el contenido de cada vista.

```php
// View::render('clientes/index', ['clientes' => $data])
// → incluye layouts/app.php que hace include('clientes/index.php')
```

---

## 8. ESTRATEGIA DE SEGURIDAD

### 8.1 Capas de seguridad

| Amenaza | Mitigación |
|---|---|
| SQL Injection | PDO + prepared statements en todos los queries |
| XSS | `htmlspecialchars()` en todas las salidas de BD en views |
| CSRF | Token por sesión en todos los formularios POST. Validado en middleware |
| Session hijacking | `session_regenerate_id(true)` en login. HTTPOnly + SameSite=Lax cookies |
| Fuerza bruta | Rate limiting por IP (contador en BD o sesión) |
| Acceso no autorizado | AuthMiddleware + RoleMiddleware en cada ruta |
| Contraseñas | `password_hash(PASSWORD_BCRYPT)`, nunca texto plano |
| Path traversal | `basename()` + whitelist de extensiones en uploads |
| Exposición de código | DocumentRoot apunta a `/public`. El resto no es accesible |

### 8.2 CSRF

```php
// En formulario:
<input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">

// En CSRFMiddleware (POST/PUT/DELETE):
CSRF::validate($_POST['csrf_token']) or abort(403);
```

### 8.3 Headers HTTP de seguridad

Enviados desde `Application::boot()`:
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'
```

---

## 9. ESTRATEGIA PWA

### 9.1 Manifest

`/public/manifest.json`:
- `name`: "Yemma ISP"
- `short_name`: "Yemma"
- `display`: "standalone"
- `start_url`: "/"
- `theme_color`: color navy del sistema
- `background_color`: color de fondo
- Iconos: 192x192, 512x512, maskable

### 9.2 Service Worker (`sw.js`)

Estrategia de caché **Cache First** para assets estáticos (CSS, JS, iconos, fuentes).
**Network First** para llamadas de datos (API endpoints).

```
Cache "yemma-static-v1" → CSS, JS, iconos, offline.html
Cache "yemma-data-v1"   → respuestas de /clientes (TTL corto)
```

Al detectar nueva versión:
- SW envía mensaje `UPDATE_AVAILABLE` al cliente
- La app muestra un toast: "Nueva versión disponible — Actualizar"
- Click → `location.reload()`

### 9.3 Pantalla offline

`/public/offline.html` se sirve cuando:
- El usuario intenta navegar sin conexión
- Y la URL no está en caché

Muestra un estado visual claro (sin conexión) con el branding del sistema.

### 9.4 Install prompt

`/public/js/pwa.js` captura `beforeinstallprompt` y lo presenta como un banner
no intrusivo: "¿Instalar Yemma en tu celular?" con botón "Instalar".

---

## 10. INTERFAZ Y DISEÑO

### 10.1 Paleta de colores (conservada del sistema actual + expandida)

```css
--navy:          #0D4A77;    /* principal, headers, botones */
--navy-light:    #1A6499;    /* hover states */
--ink:           #1C2B36;    /* texto principal */
--muted:         #6B8FA8;    /* texto secundario */
--line:          #D0DDE8;    /* bordes, divisores */
--bg:            #DFE9F0;    /* fondo de pantalla */
--surface:       #FFFFFF;    /* cards, modales */
--paid:          #2F9E63;    /* verde: pagó */
--paid-bg:       rgba(47,158,99,0.08);
--warn:          #D95B2A;    /* naranja: pendiente/alerta */
--warn-bg:       #FDF1EC;
--suspended:     #9B3B3B;    /* rojo: suspendido */
--suspended-bg:  #FDF0F0;
```

### 10.2 Tipografía (conservada)

- **Títulos / Stamps**: `Special Elite` (Google Fonts, serif vintage)
- **Números / Mono**: `IBM Plex Mono` (datos financieros, períodos)
- **UI General**: `Inter` (formularios, labels, texto corrido)

### 10.3 Componentes clave

**Client Card** (réplica del sistema actual):
```
┌─────────────────────────────────────────┐
│ [Nombre Apellido]        [  Pendiente  ] │
│ Plan · $Precio · Tel                    │
└─────────────────────────────────────────┘
```

El stamp rota `-4deg` cuando está pagado. Bordes: `2px dashed` pendiente, `2px solid` pagado.

**Bottom Sheet** (modal de detalle):
- Se desliza desde abajo (transform + transition)
- Muestra: datos del cliente, 12 meses de historial visual
- Permite registrar pago con método e importe

**Month Bar**:
```
‹  Julio 2025  ›
```
Click en el label vuelve al mes actual.

### 10.4 Mobile First

- Max-width contenido: 560px centrado
- Padding lateral: 14px
- Botones táctiles: min-height 44px (Apple HIG)
- Input search: fixed top o sticky según scroll
- Bottom actions: posición fixed, respeta safe-area-inset

---

## 11. ESTRATEGIA DE CRECIMIENTO FUTURO

### 11.1 Módulos planificados

| Prioridad | Módulo | Estado |
|---|---|---|
| Core | Gestión de clientes y pagos | Fase 1 |
| Core | Autenticación y roles | Fase 1 |
| Core | Dashboard con métricas | Fase 1 |
| Core | PWA + offline | Fase 1 |
| 2 | Reportes exportables (PDF/Excel) | Fase 2 |
| 2 | Estadísticas de cobranza | Fase 2 |
| 3 | Notificaciones WhatsApp (WAHA/wppconnect) | Fase 3 |
| 3 | Avisos automáticos de vencimiento | Fase 3 |
| 4 | Tickets de soporte técnico | Fase 4 |
| 4 | Agenda de instalaciones | Fase 4 |
| 5 | Integración con OLT/RADIUS | Fase 5 |
| 5 | Portal de cliente (autopago) | Fase 5 |

### 11.2 Puntos de extensión previstos

- **Nuevos roles**: la tabla `roles` + `configuracion.permissions` permite agregar roles sin tocar código
- **Nuevos métodos de pago**: tabla `metodos_pago`, no hardcodeado
- **Múltiples empresas (SaaS)**: columna `empresa_id` en clientes/usuarios para futura multi-tenant
- **API REST**: el Router puede responder JSON si el header Accept es `application/json`
- **Internacionalización**: no hardcodear textos de negocio; centralizarlos en un archivo de config

---

## 12. CONVENCIONES DE NOMBRES

| Elemento | Convención | Ejemplo |
|---|---|---|
| Clases PHP | PascalCase | `ClienteController`, `PagoService` |
| Métodos PHP | camelCase | `findByEstado()`, `registrarPago()` |
| Variables PHP | camelCase | `$clienteId`, `$periodoActual` |
| Constantes PHP | UPPER_SNAKE | `APP_VERSION`, `DB_HOST` |
| Tablas DB | snake_case plural | `clientes`, `historial_estados` |
| Columnas DB | snake_case | `fecha_alta`, `precio_mensual` |
| Archivos Views | kebab-case | `cliente-detalle.php` |
| Archivos Clases | PascalCase | `ClienteController.php` |
| CSS Classes | kebab-case | `.client-card`, `.month-bar` |
| URLs | kebab-case | `/clientes/nuevo`, `/pagos/historial` |
| JS funciones | camelCase | `togglePaid()`, `renderList()` |
| JS constantes | UPPER_SNAKE | `API_BASE`, `MONTH_NAMES` |

---

## 13. ROADMAP DE IMPLEMENTACIÓN

### Fase 1 — Core (este sprint)

1. Estructura de carpetas y Core framework (Router, Database, Session, Auth, CSRF, View)
2. Migraciones SQL y seeds
3. Login / Logout con roles
4. Pantalla principal: lista de clientes con stamp de pago (réplica UX)
5. AJAX: toggle pago rápido
6. CRUD de clientes (modal bottom sheet)
7. SuspensionService
8. Dashboard con métricas básicas
9. PWA: manifest + SW + offline
10. Seguridad: CSRF, XSS, headers

### Fase 2 — Administración

1. CRUD de planes
2. CRUD de usuarios y roles
3. Historial completo de pagos
4. Logs de actividad
5. Configuración de empresa
6. Reportes básicos

### Fase 3 — Comunicaciones

1. Integración WhatsApp
2. Avisos automáticos de vencimiento
3. Comprobantes de pago (upload/generación PDF)

### Fase 4 — Operaciones

1. Módulo de soporte técnico / tickets
2. Agenda de instalaciones y visitas técnicas
3. Mapa de cobertura (básico)

### Fase 5 — Escala

1. API REST documentada
2. Portal de autogestión del cliente
3. Integración con equipos de red (OLT, RADIUS)

---

*Documento generado: 2026-07-02 | Versión: 1.0*
*Revisar y actualizar ante cualquier cambio arquitectónico significativo.*
