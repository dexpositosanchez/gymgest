# GymGest Backend API

API REST para el sistema de gestión de gimnasio GymGest. Permite a entrenadores y alumnos gestionar rutinas, seguimiento y facturación.

## Stack Tecnológico

- **Framework:** Laravel 8.75
- **Lenguaje:** PHP 8.1+
- **Base de datos:** PostgreSQL 13
- **Caché:** Redis 7
- **Autenticación:** JWT (tymon/jwt-auth)
- **Arquitectura:** Hexagonal + DDD (Domain-Driven Design)
- **Documentación:** OpenAPI/Swagger
- **Contenedorización:** Docker + Docker Compose

## Arquitectura

El proyecto sigue arquitectura **hexagonal** (puertos y adaptadores) con **DDD**:

\`\`\`
app/
├── Application/         # Casos de uso (orquestación)
│   ├── UseCases/
│   └── DTOs/
├── Domain/              # Lógica de negocio pura (sin dependencias)
│   └── User/
│       ├── Entities/
│       ├── ValueObjects/
│       ├── Repositories/
│       └── Services/
└── Infrastructure/      # Adaptadores externos (frameworks, BD, HTTP)
    ├── Persistence/
    │   ├── Eloquent/
    │   └── Mappers/
    ├── Http/
    │   └── Controllers/
    └── Mail/
\`\`\`

Para más detalles, consulta \`docs/PROJECT.md\`.

## Requisitos Previos

- **Docker** y **Docker Compose** (recomendado)
- **PHP 8.1+** (si se ejecuta sin Docker)
- **Composer 2.x**
- **PostgreSQL 13** (si se ejecuta sin Docker)
- **Redis 7** (si se ejecuta sin Docker)
- **Cuenta de Gmail** con verificación en 2 pasos activada (para envío de emails)

## Instalación

### 1. Configurar variables de entorno

\`\`\`bash
cp .env.example .env
\`\`\`

Edita \`.env\` y configura las variables críticas (ver sección **Configuración** más abajo).

### 2. Generar JWT secret

\`\`\`bash
php artisan jwt:secret
\`\`\`

Esto añadirá \`JWT_SECRET\` automáticamente a tu \`.env\`.

### 3. Instalar dependencias

\`\`\`bash
composer install
\`\`\`

### 4. Ejecutar migraciones

\`\`\`bash
php artisan migrate
\`\`\`

Opcionalmente, cargar datos de prueba:

\`\`\`bash
php artisan db:seed
\`\`\`

### 5. Generar documentación Swagger

\`\`\`bash
php artisan l5-swagger:generate
\`\`\`

## Ejecución

### Con Docker (recomendado)

Desde el directorio raíz del proyecto:

\`\`\`bash
docker compose up -d backend postgres redis
\`\`\`

El backend estará disponible en el puerto **9000** (PHP-FPM).

### Sin Docker

\`\`\`bash
php artisan serve --host=0.0.0.0 --port=8000
\`\`\`

## Configuración

### Variables de entorno críticas

#### \`APP_KEY\`
Clave de cifrado de Laravel. Genera con:

\`\`\`bash
php artisan key:generate
\`\`\`

#### \`APP_URL\`
URL del backend. Por defecto: \`http://localhost\`

\`\`\`env
APP_URL=http://localhost
\`\`\`

#### \`FRONTEND_URL\`
**IMPORTANTE:** URL del frontend, usada para generar enlaces de verificación de email y recuperación de contraseña.

\`\`\`env
FRONTEND_URL=https://localhost
\`\`\`

#### \`DB_*\` (Base de datos)
Credenciales de PostgreSQL:

\`\`\`env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=gymgest
DB_USERNAME=gymgest_user
DB_PASSWORD=gymgest_password
\`\`\`

#### \`REDIS_*\` (Caché)
Configuración de Redis:

\`\`\`env
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
\`\`\`

#### \`JWT_SECRET\`
Clave secreta para firmar tokens JWT. Genera con:

\`\`\`bash
php artisan jwt:secret
\`\`\`

#### \`MAIL_*\` (Gmail SMTP)
Configuración de email para verificación de usuarios y recuperación de contraseña.

**Pasos para configurar Gmail:**

1. **Activa la verificación en 2 pasos** en tu cuenta de Google:
   https://myaccount.google.com/security

2. **Genera una contraseña de aplicación**:
   https://myaccount.google.com/apppasswords

3. **Configura las variables en \`.env\`**:

\`\`\`env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=abcd-efgh-ijkl-mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="GymGest"
\`\`\`

**Nota:** Sin esta configuración, el registro de usuarios no funcionará porque la verificación de email es obligatoria.

**Comando de prueba:**

\`\`\`bash
php artisan email:test tu@email.com
\`\`\`

#### \`IDEMPOTENCY_TTL_HOURS\`
Tiempo de vida (TTL) en horas para las claves de idempotencia. Las respuestas se cachean en Redis durante este período.

\`\`\`env
IDEMPOTENCY_TTL_HOURS=24  # Por defecto: 24 horas
\`\`\`

## Comandos Útiles (Makefile)

\`\`\`bash
# Levantar/detener contenedor
make up
make down
make restart

# Logs y shell
make logs
make shell

# Tests
make test

# Base de datos
make migrate
make seed
make fresh              # drop + migrate + seed
make db-clean          # ⚠️ DESTRUCTIVO - Vaciar todas las tablas
make db-fresh          # ⚠️ DESTRUCTIVO - Recrear BD desde cero

# Documentación y caché
make swagger
make cache-clear
\`\`\`

## Ejecución de Tests

\`\`\`bash
make test
\`\`\`

O directamente:

\`\`\`bash
./vendor/bin/phpunit
\`\`\`

**Estado actual:** 60 tests, 124 assertions ✅

## API Endpoints

La API está versionada (actualmente v1) y accesible en \`/api/v1\`.

### Autenticación (público)

- \`POST /api/v1/auth/register\` — Registro de usuario
- \`POST /api/v1/auth/login\` — Login (solo trainers)
- \`GET /api/v1/email/verify/{id}/{hash}\` — Verificación de email
- \`POST /api/v1/auth/password/email\` — Solicitar reset de contraseña
- \`POST /api/v1/auth/password/reset\` — Resetear contraseña

### Autenticación (protegido)

- \`POST /api/v1/auth/logout\` — Logout
- \`GET /api/v1/auth/me\` — Obtener usuario actual

**Nota:** Solo usuarios de tipo \`trainer\` pueden hacer login. Los \`student\` pueden registrarse pero no acceder a la aplicación web.

## Idempotencia

Todos los endpoints de escritura (POST/PUT/PATCH) soportan idempotencia opcional mediante Redis.

### Uso

Incluye el header \`Idempotency-Key\` con un UUID único por operación:

\`\`\`bash
curl -X POST https://localhost/api/v1/auth/register \\
  -H "Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000" \\
  -H "Content-Type: application/json" \\
  -d '{"email":"user@example.com", ...}'
\`\`\`

- Si envías la misma clave dos veces, recibirás la misma respuesta sin reejecutar la lógica
- Las respuestas se cachean durante 24 horas (configurable)
- El header es **opcional**

## Documentación

- **Swagger/OpenAPI:** \`https://localhost/api/documentation\` (cuando el backend está corriendo)
- **Arquitectura:** \`docs/PROJECT.md\`
- **Memoria de desarrollo:** \`docs/MEMORY.md\`
- **Tareas completadas:** \`docs/tasks/\`

## Seguridad

- JWT para autenticación
- Rate limiting en endpoints de autenticación (5 requests/minuto en login/register)
- Validación de emails obligatoria
- Cifrado AES-256-CBC para datos sensibles
- Idempotencia para prevenir duplicados

## Notas de Desarrollo

- La API está **completamente desacoplada** del frontend
- PostgreSQL es la fuente de verdad
- Redis se usa para caché, idempotencia con TTL y rate limiting
- Sigue principios SOLID y DDD
- Tests con PHPUnit

## Licencia

[Por definir]
