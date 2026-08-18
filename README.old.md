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

## Requisitos Previos

- **Docker** y **Docker Compose** (recomendado)
- **PHP 8.1+** (si se ejecuta sin Docker)
- **Composer 2.x**
- **PostgreSQL 13** (si se ejecuta sin Docker)
- **Redis 7** (si se ejecuta sin Docker)
- **Cuenta de Gmail** con verificación en 2 pasos activada (para envío de emails)

## Instalación

### 1. Configurar variables de entorno

```bash
cp .env.example .env
```

Edita `.env` y configura las variables críticas (ver sección **Configuración** más abajo).

### 2. Generar JWT secret

```bash
php artisan jwt:secret
```

Esto añadirá `JWT_SECRET` automáticamente a tu `.env`.

### 3. Instalar dependencias

```bash
composer install
```

### 4. Ejecutar migraciones

```bash
php artisan migrate
```

Opcionalmente, cargar datos de prueba:

```bash
php artisan db:seed
```

### 5. Generar documentación Swagger

```bash
php artisan l5-swagger:generate
```

## Ejecución

### Con Docker (recomendado)

Desde el directorio raíz del proyecto:

```bash
docker compose up -d backend postgres redis
```

El backend estará disponible en el puerto **9000** (PHP-FPM).

### Sin Docker

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## Configuración

### Variables de entorno críticas

#### `APP_KEY`
Clave de cifrado de Laravel. Genera con:

```bash
php artisan key:generate
```

#### `APP_URL`
URL del backend. Por defecto: `http://localhost`

```env
APP_URL=http://localhost
```

#### `FRONTEND_URL`
**IMPORTANTE:** URL del frontend, usada para generar enlaces de verificación de email y recuperación de contraseña.

```env
FRONTEND_URL=https://localhost
```

#### `DB_*` (Base de datos)
Credenciales de PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=gymgest
DB_USERNAME=gymgest_user
DB_PASSWORD=gymgest_password
```

#### `REDIS_*` (Caché)
Configuración de Redis:

```env
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### `JWT_SECRET`
Clave secreta para firmar tokens JWT. Genera con:

```bash
php artisan jwt:secret
```

#### `MAIL_*` (Gmail SMTP)
Configuración de email para verificación de usuarios y recuperación de contraseña.

**Pasos para configurar Gmail:**

1. **Activa la verificación en 2 pasos** en tu cuenta de Google:
   https://myaccount.google.com/security

2. **Genera una contraseña de aplicación**:
   https://myaccount.google.com/apppasswords

3. **Configura las variables en `.env`**:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=abcd-efgh-ijkl-mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="GymGest"
```

**Nota:** Sin esta configuración, el registro de usuarios no funcionará porque la verificación de email es obligatoria.

**Comando de prueba:**

```bash
php artisan email:test tu@email.com
```

#### `IDEMPOTENCY_TTL_HOURS`
Tiempo de vida (TTL) en horas para las claves de idempotencia. Las respuestas se cachean en Redis durante este período.

```env
IDEMPOTENCY_TTL_HOURS=24  # Por defecto: 24 horas
```

## Comandos Útiles (Makefile)

```bash
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
```

## Ejecución de Tests

```bash
make test
```

O directamente:

```bash
./vendor/bin/phpunit
```

**Estado actual:** 275 tests, 611 assertions ✅

## Datos de Desarrollo

El backend incluye un seeder con datos de desarrollo (`DevDataSeeder`) para facilitar testing y demos del sistema.

### Datos Cargados

Cuando ejecutas el `DevDataSeeder`, se crean:

#### 10 Entrenadores
- **Emails:** `trainer1@gymgest.dev` ... `trainer10@gymgest.dev`
- **Password:** `Password123!`
- **Nombre:** Trainer Number 1, Trainer Number 2, etc.
- **Género:** Distribuidos entre male, female, other (ciclo de 3)
- **Fecha nacimiento:** Años 80 (1985-01-01 ... 1985-01-09)
- **Email verificado:** ✅ Sí (todos pueden hacer login)

#### 13 Gimnasios
Distribuidos entre los entrenadores con patrón 7-2-1:
- **7 trainers con 1 gimnasio** (trainer1 ... trainer7)
- **2 trainers con 2 gimnasios** (trainer8, trainer9)
- **1 trainer con 3 gimnasios** (trainer10)

**Estructura de cada gimnasio:**
- **Nombre:** "Gym {N} - Trainer{X}" (ej: "Gym 1 - Trainer1")
- **Dirección:** "Calle Fitness {N}, {N}"
- **Localidad/Provincia:** Rotación entre Madrid, Barcelona, Valencia, Sevilla
- **País:** España
- **Estado:** Todos activos (`is_active = true`)

#### 30 Alumnos (Students)
- **Emails:** `student1@gymgest.dev` ... `student30@gymgest.dev`
- **Password:** `Password123!`
- **Nombre:** Student Number 1, Student Number 2, etc.
- **Género:** Distribuidos entre male, female, other (ciclo de 3)
- **Fecha nacimiento:** Años 90 (1995-01-15 ... 1995-09-15)
- **Gym Goals:** "Mejorar condición física"
- **Email verificado:** ✅ Sí
- **Nota:** Los students NO pueden hacer login en la app web (solo trainers)

#### 15 Asignaciones Gimnasio-Alumno
Solo los primeros 15 alumnos están asignados a gimnasios, con diferentes estados de cuota:

**5 alumnos con cuota vigente (>30 días):**
- student1@gymgest.dev ... student5@gymgest.dev
- Cuota expira en: 31-35 días desde hoy
- Estado: `is_active = true`, `quota_status = 'active'`

**5 alumnos con cuota próxima a caducar (1-7 días):**
- student6@gymgest.dev ... student10@gymgest.dev
- Cuota expira en: 1-5 días desde hoy
- Estado: `is_active = true`, `quota_status = 'expiring_soon'`

**3 alumnos con cuota caducada (fecha pasada):**
- student11@gymgest.dev ... student13@gymgest.dev
- Cuota expiró hace: 1-3 días
- Estado: `is_active = true`, `quota_status = 'expired'`

**2 alumnos inactivos (dados de baja):**
- student14@gymgest.dev, student15@gymgest.dev
- Cuota expiró hace: 30 días
- Estado: `is_active = false`, `quota_status = 'inactive'`

**Los alumnos 16-30** NO están asignados a ningún gimnasio.

### Cómo Cargar los Datos

#### Opción 1: Con `make up` (recomendado)

Al levantar el backend con `make up`, el Makefile preguntará si quieres cargar datos:

```bash
cd backend
make up

# Tras levantar el backend:
# ¿Cargar datos de desarrollo? (s/n)
# (10 trainers, 13 gyms, 30 students con diferentes estados de cuota)
```

Responde `s` o `S` para cargar. El sistema espera 5 segundos y ejecuta el seeder automáticamente.

#### Opción 2: Manualmente con artisan

```bash
# Dentro del contenedor
docker exec gymgest_backend php artisan db:seed --class=DevDataSeeder --force

# O desde el directorio backend/
php artisan db:seed --class=DevDataSeeder
```

#### Opción 3: Automáticamente en primer arranque

La **primera vez** que levantas el stack con base de datos vacía, el script `entrypoint.sh` preguntará interactivamente si quieres cargar datos de desarrollo tras ejecutar los seeders base (muscle groups + exercises).

### Idempotencia del Seeder

El `DevDataSeeder` usa `firstOrCreate()` con el email como clave única, por lo que:
- ✅ Puedes ejecutarlo múltiples veces sin crear duplicados
- ✅ Si los datos ya existen, no hace nada
- ✅ Si faltan algunos datos, solo crea los que faltan

### Credenciales de Acceso

**Password universal:** `Password123!`

**Trainers (pueden hacer login):**
```
trainer1@gymgest.dev  ... trainer10@gymgest.dev
```

**Students (NO pueden hacer login en web):**
```
student1@gymgest.dev  ... student30@gymgest.dev
```

### Datos Base Obligatorios

Además del `DevDataSeeder`, el sistema requiere datos base que se cargan automáticamente con:

```bash
php artisan db:seed
```

Esto carga:
- **16 grupos musculares** (Pecho, Espalda, Deltoides, Bíceps, Tríceps, etc.)
- **62 ejercicios por defecto** distribuidos entre los grupos musculares

Estos datos son **obligatorios** para que el sistema funcione correctamente (sin ellos, no puedes crear rutinas).

## API Endpoints

La API está versionada (actualmente v1) y accesible en `/api/v1`.

### Autenticación (público)

- `POST /api/v1/auth/register` — Registro de usuario
- `POST /api/v1/auth/login` — Login (solo trainers)
- `GET /api/v1/email/verify/{id}/{hash}` — Verificación de email
- `POST /api/v1/auth/password/email` — Solicitar reset de contraseña
- `POST /api/v1/auth/password/reset` — Resetear contraseña

### Autenticación (protegido)

- `POST /api/v1/auth/logout` — Logout
- `GET /api/v1/auth/me` — Obtener usuario actual

**Nota:** Solo usuarios de tipo `trainer` pueden hacer login. Los `student` pueden registrarse pero no acceder a la aplicación web.

## Idempotencia

Todos los endpoints de escritura (POST/PUT/PATCH) soportan idempotencia opcional mediante Redis.

### Uso

Incluye el header `Idempotency-Key` con un UUID único por operación:

```bash
curl -X POST https://localhost/api/v1/auth/register \\
  -H "Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000" \\
  -H "Content-Type: application/json" \\
  -d '{"email":"user@example.com", ...}'
```

- Si envías la misma clave dos veces, recibirás la misma respuesta sin reejecutar la lógica
- Las respuestas se cachean durante 24 horas (configurable)
- El header es **opcional**

## Documentación

- **Swagger/OpenAPI:** `https://localhost/api/documentation` (cuando el backend está corriendo)
- **Arquitectura:** `docs/PROJECT.md`
- **Memoria de desarrollo:** `docs/MEMORY.md`
- **Tareas completadas:** `docs/tasks/`

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
