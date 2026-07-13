# 🐛 BUGFIXES - Login de Alumnos y Cache

## Fecha: 2026-07-13

## Bugs identificados y resueltos

### 🔴 BUG #1: Login de alumnos bloqueado en API
**Severidad:** CRÍTICA
**Impacto:** Los alumnos no podían hacer login en la API

#### Problema
El `LoginUserUseCase` bloqueaba el login de estudiantes con el error:
```
"Esta aplicación es solo para entrenadores"
```

Esto es incorrecto porque:
- La API debe servir tanto a trainers como a students
- El middleware `StudentOnly` ya protege los endpoints específicos de alumnos
- El middleware `EnsureUserIsTrainer` ya protege los endpoints de trainers
- La restricción debe estar en el FRONTEND, no en el login de la API

#### Solución
**Archivo:** `app/Application/UseCases/LoginUserUseCase.php`

**Cambio realizado:**
```php
// ❌ ANTES (líneas 32-35)
// Verificar que el usuario es trainer
if (!$user->getUserType()->isTrainer()) {
    throw new \DomainException('Esta aplicación es solo para entrenadores');
}

// ✅ DESPUÉS
// (Se eliminó la validación de tipo de usuario)
```

**Resultado:**
- ✅ Trainers pueden hacer login (como antes)
- ✅ Students pueden hacer login (nuevo)
- ✅ La protección de endpoints sigue funcionando con middlewares

---

### 🔴 BUG #2: Error de Cache al matricular alumnos
**Severidad:** CRÍTICA
**Impacto:** Crashes al matricular alumnos o asignar rutinas

#### Problema
Al matricular un alumno o asignar una rutina, el sistema crasheaba con:
```
Call to undefined method Illuminate\Cache\FileStore::getRedis()
```

**Causa raíz:**
El `RoutineAssignmentCacheService` intentaba llamar a `getRedis()` incluso cuando el driver de caché configurado era `file` (en lugar de `redis`).

#### Solución
**Archivo:** `app/Application/RoutineAssignment/Services/RoutineAssignmentCacheService.php`

**Cambio realizado:**
```php
// ❌ ANTES
if (config('cache.default') === 'array') {
    Cache::flush();
} else {
    $keys = Cache::getRedis()->keys($pattern);  // ❌ Crash si driver != redis
    if (!empty($keys)) {
        Cache::getRedis()->del($keys);
    }
}

// ✅ DESPUÉS
$cacheDriver = config('cache.default');

if (in_array($cacheDriver, ['array', 'file'])) {
    // Para array/file cache, flush todo
    Cache::flush();
} elseif ($cacheDriver === 'redis') {
    // Solo para Redis, usar pattern matching
    $redis = Cache::getStore()->getRedis();
    $keys = $redis->keys($pattern);
    if (!empty($keys)) {
        $redis->del($keys);
    }
} else {
    // Otros drivers (memcached, database)
    Cache::flush();
}
```

**Resultado:**
- ✅ Funciona con cache `file` (desarrollo)
- ✅ Funciona con cache `redis` (producción)
- ✅ Funciona con cache `array` (tests)
- ✅ Fallback seguro para otros drivers

---

### 🟡 BUG #3: Modal de matriculación sin feedback visual
**Severidad:** MENOR (UX)
**Impacto:** Usuario no veía feedback al matricular

#### Problema
Al matricular un alumno:
1. Usuario hace click en "Matricular"
2. No pasa nada visible
3. Usuario cierra modal
4. Alumno aparece tras refresh

**Causa:**
El componente `EnrollStudentModal` SÍ tiene loading state, pero el error de cache (#2) hacía que fallara silenciosamente.

#### Solución
Al arreglar el bug #2 de cache, este problema se resolvió automáticamente.

**Verificación:**
El modal ya tiene:
- ✅ Loading spinner (líneas 241-244)
- ✅ Loading state en botones (línea 238)
- ✅ Disabled state durante submit (líneas 146, 168, 216, 231)

---

### 🔧 Cambio asociado: Test actualizado

**Archivo:** `tests/Feature/AuthenticationTest.php`

El test `test_student_user_cannot_login()` fue renombrado a `test_student_user_can_login()` y actualizado para reflejar el nuevo comportamiento:

```php
// ✅ AHORA
public function test_student_user_can_login()
{
    // Create a verified student user
    $user->email_verified_at = now(); // ← Email verificado

    // Students can now login
    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'user' => ['id', 'email', 'user_type', 'name', 'last_name']
        ]);
}
```

---

## ✅ Tests ejecutados

### Suite completa: **333/333 pasando** ✅

```bash
docker exec gymgest_backend ./vendor/bin/phpunit --testdox
```

**Resultado:**
```
OK (333 tests, 830 assertions)
Time: 00:18.314s
```

---

## 📋 Archivos modificados

1. `app/Application/UseCases/LoginUserUseCase.php`
2. `app/Application/RoutineAssignment/Services/RoutineAssignmentCacheService.php`
3. `tests/Feature/AuthenticationTest.php`

---

## 🚀 Cómo probar los fixes

### 1. Login de alumno (Postman)

```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "student.task027@test.com",
  "password": "password123"
}

# ✅ Debe devolver: 200 OK con access_token
```

### 2. Matricular alumno (Frontend)

1. Login como trainer
2. Ir a "Alumnos"
3. Click en "Matricular nuevo alumno"
4. Llenar formulario
5. Click en "Matricular"
6. ✅ Debe mostrar spinner y cerrarse automáticamente
7. ✅ Alumno aparece en la lista sin refresh

### 3. Asignar rutina (Frontend)

1. Login como trainer
2. Ir a "Alumnos" → seleccionar alumno
3. Tab "Rutinas asignadas"
4. Click en "Asignar rutina"
5. Llenar formulario
6. Click en "Asignar"
7. ✅ Debe mostrar spinner y cerrarse automáticamente
8. ✅ Rutina aparece en la lista sin refresh

---

## 🎯 Verificación de no-regresión

### Endpoints que siguen protegidos correctamente:

| Endpoint | Trainer | Student | Sin Auth |
|----------|---------|---------|----------|
| `POST /auth/login` | ✅ | ✅ | ✅ |
| `GET /gyms` | ✅ | ❌ 403 | ❌ 401 |
| `POST /gyms` | ✅ | ❌ 403 | ❌ 401 |
| `GET /students/me/routines` | ❌ 403 | ✅ | ❌ 401 |
| `POST /gyms/{id}/students` | ✅ | ❌ 403 | ❌ 401 |

---

## 🔄 Configuración de cache recomendada

### Desarrollo (local)
```env
CACHE_DRIVER=file
```

### Testing (CI/CD)
```env
CACHE_DRIVER=array
```

### Producción
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 📝 Notas importantes

1. **Seguridad:** Los middlewares siguen protegiendo los endpoints correctamente
2. **Cache:** Ahora es compatible con múltiples drivers (file, redis, array)
3. **Tests:** Todos los tests existentes siguen pasando
4. **Backward compatibility:** No se rompió ninguna funcionalidad existente

---

## ✨ Próximos pasos sugeridos

1. ✅ Verificar que el frontend muestra mensajes toast de éxito/error
2. ✅ Considerar añadir logs para monitorizar invalidaciones de cache
3. ✅ Documentar la política de cache en la wiki del proyecto
