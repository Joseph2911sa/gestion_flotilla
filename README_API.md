# Gestión Flotilla — Backend API

Sistema de gestión de flotilla vehicular desarrollado con **Laravel 12** como API REST. Este repositorio corresponde exclusivamente al backend; el frontend se encuentra en el repositorio `gestion-flotilla-web`.

---

## Descripción general del sistema

El sistema permite gestionar la flotilla vehicular de una organización. Contempla tres roles de usuario (Admin, Operador y Chofer) con permisos diferenciados:

- **Admin**: gestión completa de usuarios, vehículos, rutas, mantenimientos, solicitudes de viaje y reportes.
- **Operador**: gestión de solicitudes, viajes, rutas y mantenimientos.
- **Chofer**: consulta de vehículos, creación de solicitudes de viaje y revisión de historial.

El flujo principal del negocio es:

1. Un Chofer crea una **solicitud de viaje** (o un Operador la crea directamente con asignación de vehículo y chofer).
2. El Operador/Admin **aprueba o rechaza** la solicitud.
3. Al aprobarla se genera un **viaje** activo.
4. El Chofer **registra el retorno** del viaje, lo que actualiza automáticamente el estado de la solicitud a `completed` mediante un trigger de base de datos.

---

## Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Lenguaje | PHP 8.2+ |
| Framework | Laravel 12 |
| Autenticación | Laravel Sanctum (tokens Bearer) |
| Base de datos | PostgreSQL 15+ |
| ORM | Eloquent |
| Lógica en BD | Funciones PL/pgSQL, Procedimiento almacenado, Trigger |
| Servidor local | `php artisan serve` |

### Dependencias principales (`composer.json`)

```
laravel/framework   ^12.0
laravel/sanctum     ^4.3
laravel/tinker      ^2.10.1
```

---

## Requisitos previos

- PHP >= 8.2 con extensiones: `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`
- Composer >= 2.x
- PostgreSQL >= 15 en ejecución local (puerto 5432)
- Node.js >= 18 y npm (para compilar assets opcionales)

---

## Clonar el repositorio

```bash
git clone <URL-del-repo-api> gestion-flotilla-api
cd gestion-flotilla-api
```

---

## Instalar dependencias

```bash
composer install
```

---

## Configuración de variables de entorno

Copiar el archivo de ejemplo y editarlo:

```bash
cp .env.example .env
```

Abrir `.env` y ajustar los valores marcados con `← CAMBIAR`:

```dotenv
APP_NAME="Gestion Flotilla API"
APP_ENV=local
APP_KEY=                          # se genera automáticamente (ver siguiente sección)
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=America/Costa_Rica

# ── Base de datos ─────────────────────────────────────────────────────────────
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sistema_flotilla_pa   # ← CAMBIAR si usas otro nombre de BD
DB_USERNAME=postgres               # ← CAMBIAR según tu usuario de PostgreSQL
DB_PASSWORD=sa1234                 # ← CAMBIAR según tu contraseña

# ── Sanctum (sesión / CORS) ───────────────────────────────────────────────────
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=127.0.0.1

# ── Cola de trabajos ──────────────────────────────────────────────────────────
QUEUE_CONNECTION=database
CACHE_STORE=database
```

> **Nota:** El frontend consume esta API desde `http://127.0.0.1:8000/api/v1`. Si cambias el puerto, actualiza también `FLOTILLA_API_URL` en el `.env` del repo web.

---

## Generar clave de la aplicación

```bash
php artisan key:generate
```

---

## Crear la base de datos en PostgreSQL

Antes de ejecutar migraciones, crear la base de datos manualmente:

```sql
-- Conectarse a PostgreSQL como superusuario:
psql -U postgres
CREATE DATABASE sistema_flotilla_pa;
\q
```

---

## Ejecutar migraciones y seeders

```bash
# Crea todas las tablas, funciones, procedimiento almacenado y trigger:
php artisan migrate

# Pobla los roles iniciales (Admin, Operador, Chofer):
php artisan db:seed
```

Las migraciones crean, en orden:

| Migración | Qué crea |
|---|---|
| `create_users_table` | Tabla de usuarios |
| `create_roles_table` | Tabla de roles |
| `add_role_id_to_users_table` | Relación usuario → rol |
| `create_vehicles_table` | Tabla de vehículos (con soft delete) |
| `create_routes_table` | Tabla de rutas |
| `create_trip_requests_table` | Tabla de solicitudes de viaje |
| `create_trips_table` | Tabla de viajes (registro de salida/retorno) |
| `create_maintenances_table` | Tabla de mantenimientos |
| `create_personal_access_tokens_table` | Tokens Sanctum |
| `create_fn_vehicle_available` | **Función PL/pgSQL** `fn_vehicle_available()` — valida disponibilidad de vehículo |
| `create_sp_approve_trip_request` | **Procedimiento almacenado** `sp_approve_trip_request()` — aprobación con validaciones completas |
| `create_trip_request_status_trigger` | **Trigger** `trigger_update_trip_request_status` — marca la solicitud como `completed` al registrar el retorno |

El seeder crea los 3 roles en la tabla `roles`. Los usuarios de prueba deben crearse manualmente vía la API o Tinker.

---

## Crear un usuario administrador inicial (opcional)

```bash
php artisan tinker
```

```php
use App\Models\User;
use App\Models\Role;

$admin = User::create([
    'name'     => 'Administrador',
    'email'    => 'admin@flotilla.com',
    'password' => bcrypt('password'),
    'role_id'  => Role::where('name', 'Admin')->first()->id,
]);
```

---

## Ejecutar el proyecto localmente

```bash
php artisan serve
# La API queda disponible en: http://127.0.0.1:8000/api/v1
```

Para desarrollo con logs en tiempo real:

```bash
composer run dev
# Levanta en paralelo: servidor PHP, cola de trabajos y log viewer (pail)
```

---

## Endpoints principales de la API

Todos los endpoints (excepto `login`) requieren el header:

```
Authorization: Bearer <token>
```

| Método | Ruta | Roles | Descripción |
|---|---|---|---|
| POST | `/api/v1/login` | público | Autenticación, retorna token |
| POST | `/api/v1/logout` | todos | Cierra sesión |
| GET | `/api/v1/users` | Admin, Operador | Listar usuarios |
| POST | `/api/v1/users` | Admin | Crear usuario |
| GET | `/api/v1/vehicles` | todos | Listar vehículos |
| GET | `/api/v1/vehicles/{id}/availability` | todos | Verificar disponibilidad (usa `fn_vehicle_available`) |
| GET | `/api/v1/routes` | Admin, Operador | Listar rutas |
| GET | `/api/v1/trip-requests` | todos | Listar solicitudes |
| POST | `/api/v1/trip-requests` | todos | Crear solicitud |
| POST | `/api/v1/trip-requests/direct-assign` | Admin, Operador | Asignación directa |
| PATCH | `/api/v1/trip-requests/{id}/approve` | Admin, Operador | Aprobar solicitud (vía Laravel) |
| POST | `/api/v1/trip-requests/{id}/approve-db` | Admin, Operador | Aprobar solicitud (vía `sp_approve_trip_request`) |
| PATCH | `/api/v1/trip-requests/{id}/reject` | Admin, Operador | Rechazar solicitud |
| PATCH | `/api/v1/trip-requests/{id}/cancel` | todos | Cancelar solicitud |
| GET | `/api/v1/trips` | Admin, Operador | Listar viajes |
| PATCH | `/api/v1/trips/{id}/register-return` | todos | Registrar retorno (activa trigger) |
| GET | `/api/v1/maintenances` | Admin, Operador | Listar mantenimientos |
| PATCH | `/api/v1/maintenances/{id}/close` | Admin, Operador | Cerrar mantenimiento |
| GET | `/api/v1/reports/vehicle-availability` | todos | Reporte disponibilidad |
| GET | `/api/v1/reports/fleet-usage` | Admin, Operador | Reporte uso flotilla |
| GET | `/api/v1/reports/driver-history` | Admin, Operador | Historial de choferes |

---

## Estructura del proyecto

```
gestion-flotilla-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/               ← Controladores REST (un archivo por recurso)
│   │   │       ├── AuthController.php
│   │   │       ├── VehicleController.php
│   │   │       ├── VehicleAvailabilityController.php
│   │   │       ├── TripRequestController.php
│   │   │       ├── TripController.php
│   │   │       ├── MaintenanceController.php
│   │   │       ├── RouteController.php
│   │   │       ├── UserController.php
│   │   │       └── ReportController.php
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php  ← Protección por rol (Admin/Operador/Chofer)
│   │   └── Requests/              ← Validaciones (Form Requests)
│   └── Models/                    ← Modelos Eloquent
│       ├── User.php
│       ├── Vehicle.php
│       ├── TripRequest.php
│       ├── Trip.php
│       ├── Maintenance.php
│       ├── Route.php
│       └── Role.php
├── database/
│   ├── migrations/                ← Tablas + función + SP + trigger
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── RoleSeeder.php
├── routes/
│   └── api.php                    ← Definición completa de endpoints
├── .env.example                   ← Plantilla de variables de entorno
└── composer.json
```

---

## Objetos de base de datos

El sistema implementa tres objetos directamente en PostgreSQL:

### Función: `fn_vehicle_available(vehicle_id, departure, return)`
Retorna `TRUE` si el vehículo está disponible para el rango de fechas dado. Verifica: que el vehículo exista y esté en estado `available`, que no tenga mantenimientos abiertos y que no existan solicitudes aprobadas con fechas que se traslapen.

### Procedimiento almacenado: `sp_approve_trip_request(trip_request_id, reviewed_by)`
Ejecuta la aprobación completa de una solicitud con todas las validaciones de negocio en la base de datos. Reutiliza `fn_vehicle_available` internamente. Accesible vía `POST /api/v1/trip-requests/{id}/approve-db`.

### Trigger: `trigger_update_trip_request_status`
Se dispara al actualizar la tabla `trips`. Cuando se registra el `end_time` del viaje, automáticamente cambia el estado de la solicitud asociada a `completed`.

---

## Roles y permisos resumidos

| Acción | Admin | Operador | Chofer |
|---|:---:|:---:|:---:|
| CRUD usuarios | ✅ | — | — |
| CRUD vehículos | ✅ | ✅ | — |
| CRUD rutas | ✅ | ✅ | — |
| Ver vehículos | ✅ | ✅ | ✅ |
| Crear solicitud | ✅ | ✅ | ✅ |
| Aprobar/rechazar solicitud | ✅ | ✅ | — |
| Asignación directa | ✅ | ✅ | — |
| Registrar retorno viaje | ✅ | ✅ | ✅ |
| CRUD mantenimientos | ✅ | ✅ | — |
| Reportes completos | ✅ | ✅ | — |
| Reporte disponibilidad | ✅ | ✅ | ✅ |
