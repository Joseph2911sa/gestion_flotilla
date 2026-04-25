# Gestión Flotilla — Frontend Web

Aplicación web desarrollada con **Laravel 12 + Blade** que actúa como cliente del backend API (`gestion-flotilla-api`). Consume la API REST mediante **Guzzle HTTP** y renderiza las vistas del sistema en el servidor.

> Este repositorio **no tiene base de datos propia**. Toda la persistencia ocurre en el backend API.

---

## Descripción general del sistema

El frontend web provee la interfaz de usuario para el sistema de gestión de flotilla vehicular. Implementa autenticación por sesión (guardando el token Sanctum en `session()`), y protege las rutas según el rol del usuario autenticado.

Los tres roles del sistema acceden a secciones diferenciadas:

- **Admin**: usuarios, vehículos, rutas, mantenimientos, solicitudes (vía panel operador) y reportes.
- **Operador**: solicitudes de viaje, asignación directa, viajes, rutas y mantenimientos.
- **Chofer**: consulta de vehículos disponibles, solicitudes propias e historial de viajes.

El flujo de autenticación es:
1. El usuario ingresa credenciales en `/login`.
2. El `AuthWebController` hace `POST /api/v1/login` al backend.
3. El token recibido se guarda en la sesión de Laravel (`session(['api_token' => ...])`).
4. Cada request posterior adjunta el token en el header `Authorization: Bearer`.

---

## Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Lenguaje | PHP 8.2+ |
| Framework | Laravel 12 |
| Motor de vistas | Blade (renderizado en servidor) |
| Cliente HTTP | Guzzle HTTP 7.x |
| Estilos | Bootstrap 5 (CDN) |
| Autenticación | Sesión Laravel + token Sanctum (del backend) |
| Servidor local | `php artisan serve --port=8001` |

### Dependencias principales (`composer.json`)

```
laravel/framework    ^12.0
guzzlehttp/guzzle    ^7.0
laravel/tinker       ^2.10.1
```

---

## Requisitos previos

- PHP >= 8.2 con extensiones: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`
- Composer >= 2.x
- Node.js >= 18 y npm
- El backend `gestion-flotilla-api` debe estar ejecutándose en `http://127.0.0.1:8000`

> **Importante:** el frontend no necesita PostgreSQL. Solo necesita que la API esté levantada.

---

## Clonar el repositorio

```bash
git clone <URL-del-repo-web> gestion-flotilla-web
cd gestion-flotilla-web
```

---

## Instalar dependencias

```bash
composer install
npm install
```

Alternativamente, ejecutar el script de setup incluido:

```bash
# En Linux/macOS:
bash setup.sh

# En Windows:
setup.bat
```

El script realiza en orden: `composer install`, instalación de Guzzle, copia del `.env`, generación de `APP_KEY`, permisos de storage y limpieza de caché.

---

## Configuración de variables de entorno

Copiar el archivo de ejemplo y editarlo:

```bash
cp .env.example .env
```

Ajustar los valores relevantes:

```dotenv
APP_NAME="Gestion Flotilla Web"
APP_ENV=local
APP_KEY=                          # se genera automáticamente
APP_DEBUG=true
APP_URL=http://127.0.0.1:8001    # ← Puerto del frontend

APP_TIMEZONE=America/Costa_Rica

# ── El frontend NO tiene BD propia ───────────────────────────────────────────
# Las líneas de DB_* deben permanecer comentadas.

SESSION_DRIVER=file
SESSION_LIFETIME=120

QUEUE_CONNECTION=sync
CACHE_STORE=file

# ── URL del backend API ───────────────────────────────────────────────────────
FLOTILLA_API_URL=http://127.0.0.1:8000/api/v1   # ← CAMBIAR si el API corre en otro puerto
```

> La variable `FLOTILLA_API_URL` es la única conexión entre el frontend y el backend. Todos los controladores web la leen a través de `env('FLOTILLA_API_URL')` para construir las llamadas Guzzle.

---

## Generar clave de la aplicación

```bash
php artisan key:generate
```

---

## Compilar assets

```bash
# Para desarrollo (con hot reload):
npm run dev

# Para producción:
npm run build
```

---

## Ejecutar el proyecto localmente

Asegurarse primero de que el backend esté corriendo:

```bash
# En la carpeta del backend (gestion-flotilla-api):
php artisan serve   # → http://127.0.0.1:8000
```

Luego levantar el frontend:

```bash
# En la carpeta del frontend (gestion-flotilla-web):
php artisan serve --port=8001
# La aplicación queda disponible en: http://127.0.0.1:8001
```

Para desarrollo con logs en tiempo real:

```bash
composer run dev
# Levanta en paralelo: servidor PHP en :8001, log viewer y Vite
```

---

## Estructura del proyecto

```
gestion-flotilla-web/
├── app/
│   ├── Helpers/
│   │   └── helpers.php                   ← Helper global para llamadas Guzzle a la API
│   └── Http/
│       ├── Controllers/
│       │   ├── AuthWebController.php      ← Login y logout (consume POST /api/v1/login)
│       │   ├── DashboardController.php    ← Dashboard general
│       │   ├── Admin/
│       │   │   ├── UsuarioController.php  ← CRUD usuarios (Admin)
│       │   │   ├── VehiculoController.php ← CRUD vehículos (Admin)
│       │   │   ├── RutaController.php     ← CRUD rutas (Admin)
│       │   │   ├── MantenimientoController.php ← Mantenimientos (Admin)
│       │   │   └── ReporteController.php  ← Reportes (Admin)
│       │   ├── Operador/
│       │   │   ├── SolicitudController.php ← Solicitudes: aprobar, rechazar, asignación directa
│       │   │   ├── ViajeController.php    ← Viajes: crear, registrar retorno
│       │   │   ├── RutaController.php     ← Rutas (Operador)
│       │   │   └── MantenimientoController.php ← Mantenimientos (Operador)
│       │   └── Chofer/
│       │       ├── VehiculoController.php ← Ver vehículos disponibles
│       │       ├── SolicitudController.php ← Crear y ver solicitudes propias
│       │       └── HistorialController.php ← Historial de viajes y cancelar
│       └── Middleware/
│           └── AuthWebMiddleware.php      ← Protección por sesión y rol
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php              ← Layout principal (sidebar, navbar)
│       │   └── guest.blade.php            ← Layout para login
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── operador/
│       │   ├── solicitudes/index.blade.php
│       │   ├── viajes/index.blade.php
│       │   ├── rutas/index.blade.php
│       │   └── mantenimientos/index.blade.php
│       ├── chofer/
│       │   ├── solicitudes/index.blade.php
│       │   ├── vehiculos/index.blade.php
│       │   └── historial/index.blade.php
│       └── welcome.blade.php
├── routes/
│   └── web.php                            ← Todas las rutas web organizadas por rol
├── setup.sh                               ← Script de instalación (Linux/macOS)
├── setup.bat                              ← Script de instalación (Windows)
├── .env.example                           ← Plantilla de variables de entorno
└── composer.json
```

---

## Rutas disponibles

### Públicas

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/login` | Formulario de inicio de sesión |
| POST | `/login` | Procesa autenticación |
| POST | `/logout` | Cierra sesión |

### Protegidas — Admin (`/admin/...`)

| Método | Ruta | Descripción |
|---|---|---|
| GET/POST | `/admin/usuarios` | Listar y crear usuarios |
| GET/PUT/DELETE | `/admin/usuarios/{id}` | Editar y eliminar usuario |
| GET/POST | `/admin/vehiculos` | Listar y crear vehículos |
| GET/PUT/DELETE | `/admin/vehiculos/{id}` | Editar y eliminar vehículo |
| GET/POST/PUT/DELETE | `/admin/rutas` | CRUD de rutas |
| GET/POST | `/admin/mantenimientos` | Listar y crear mantenimientos |
| PATCH | `/admin/mantenimientos/{id}/cerrar` | Cerrar mantenimiento |
| GET | `/admin/reportes` | Panel de reportes |
| GET | `/admin/reportes/disponibilidad` | Reporte de disponibilidad |
| GET | `/admin/reportes/uso-flotilla` | Reporte uso de flotilla |
| GET | `/admin/reportes/historial-chofer` | Historial de choferes |

### Protegidas — Operador (`/operador/...`)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/operador/solicitudes` | Listar solicitudes |
| PATCH | `/operador/solicitudes/{id}/aprobar` | Aprobar solicitud |
| PATCH | `/operador/solicitudes/{id}/rechazar` | Rechazar solicitud |
| GET/POST | `/operador/solicitudes/asignacion-directa` | Formulario y creación de asignación directa |
| GET/POST | `/operador/viajes` | Listar y crear viajes |
| PATCH | `/operador/viajes/{id}/retorno` | Registrar retorno |
| GET/POST/PUT/DELETE | `/operador/rutas` | CRUD de rutas |
| GET/POST | `/operador/mantenimientos` | Listar y crear mantenimientos |
| PATCH | `/operador/mantenimientos/{id}/cerrar` | Cerrar mantenimiento |

### Protegidas — Chofer (`/chofer/...`)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/chofer/vehiculos` | Ver vehículos disponibles |
| GET/POST | `/chofer/solicitudes` | Ver y crear solicitudes propias |
| GET | `/chofer/historial` | Ver historial de viajes |
| POST | `/chofer/historial/{id}/cancelar` | Cancelar solicitud |

---

## Middleware de autenticación

El archivo `AuthWebMiddleware.php` protege todas las rutas del grupo `auth.web`. Verifica que exista un token en sesión y, opcionalmente, valida que el rol del usuario coincida con los roles permitidos por la ruta.

Ejemplo de uso en `web.php`:

```php
// Solo Admin puede acceder:
Route::middleware('auth.web:Admin')->prefix('admin')-> ...

// Admin y Operador pueden acceder:
Route::middleware('auth.web:Admin,Operador')->prefix('operador')-> ...
```

---

## Notas de desarrollo

- El frontend **no ejecuta migraciones** (`php artisan migrate` no aplica aquí).
- Si se cambia el puerto del backend, actualizar `FLOTILLA_API_URL` en `.env` y limpiar la caché: `php artisan config:clear`.
- Las imágenes de vehículos se sirven directamente desde el storage del backend (`http://127.0.0.1:8000/storage/vehicles/...`).
- Para pruebas rápidas, el helper global en `app/Helpers/helpers.php` centraliza la construcción de requests Guzzle con el token de sesión.
