# 🐛 BUGFIX - Endpoint de Desactivar Alumno (404)

## Fecha: 2026-07-13

## Bug identificado y resuelto

### 🔴 BUG #4: Endpoint deactivate daba 404
**Severidad:** ALTA
**Impacto:** No se podía desactivar alumnos desde la API (colección Postman fallaba)

#### Problema
El endpoint `PUT /api/v1/gyms/{gymId}/students/{studentId}/deactivate` devolvía **404 Not Found**.

**Causa raíz:**
- El método `deactivate()` existía en el controlador (inyección en constructor)
- El `DeactivateStudentUseCase` existía y funcionaba
- ❌ **Faltaba el método público en el controlador**
- ❌ **Faltaba la ruta en `routes/api_v1.php`**

#### Solución implementada

**1. Archivo:** `app/Infrastructure/Http/Controllers/V1/GymStudentController.php`

Agregado método público `deactivate()` (líneas 291-331):

```php
/**
 * @OA\Put(
 *     path="/api/v1/gyms/{gymId}/students/{studentId}/deactivate",
 *     summary="Dar de baja a un alumno activo",
 *     tags={"Gym Students"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="gymId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Parameter(
 *         name="studentId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(response=204, description="Alumno desactivado"),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="No autorizado"),
 *     @OA\Response(response=404, description="Alumno no encontrado")
 * )
 */
public function deactivate(string $gymId, string $studentId): JsonResponse
{
    try {
        $trainerId = auth()->id();
        $this->deactivateStudentUseCase->execute($gymId, $studentId, $trainerId);

        return response()->json(null, 204);
    } catch (InvalidArgumentException $e) {
        if ($e->getMessage() === 'Gym not found' || $e->getMessage() === 'Student not enrolled in this gym') {
            return response()->json(['error' => $e->getMessage()], 404);
        }
        if ($e->getMessage() === 'Unauthorized') {
            return response()->json(['error' => $e->getMessage()], 403);
        }
        return response()->json(['error' => $e->getMessage()], 422);
    }
}
```

**2. Archivo:** `routes/api_v1.php`

Agregada ruta (línea 59):

```php
// Gym Students routes
Route::get('gyms/{gymId}/students', [GymStudentController::class, 'index']);
Route::post('gyms/{gymId}/students', [GymStudentController::class, 'store']);
Route::put('gyms/{gymId}/students/{studentId}', [GymStudentController::class, 'update']);
Route::delete('gyms/{gymId}/students/{studentId}', [GymStudentController::class, 'destroy']);
Route::put('gyms/{gymId}/students/{studentId}/deactivate', [GymStudentController::class, 'deactivate']); // ✅ NUEVA
Route::put('gyms/{gymId}/students/{studentId}/reactivate', [GymStudentController::class, 'reactivate']);
```

---

## ✅ Verificación

### 1. Ruta registrada correctamente

```bash
docker exec gymgest_backend php artisan route:list --path=deactivate
```

**Resultado:**
```
PUT  api/v1/gyms/{gymId}/students/{studentId}/deactivate
Action: GymStudentController@deactivate
Middleware: api, Authenticate, EnsureUserIsTrainer ✅
```

### 2. Tests pasando

```bash
docker exec gymgest_backend ./vendor/bin/phpunit --testdox
```

**Resultado:**
```
✅ OK (333 tests, 830 assertions)
Time: 00:35s
```

---

## 🚀 Cómo probar el fix

### Opción 1: Postman (Colección TASK_027)

```bash
# 3.3 - Invalidar caché (desactivar alumno)
PUT /api/v1/gyms/{gymId}/students/{studentId}/deactivate
Authorization: Bearer {trainer_token}

# ✅ Debe devolver: 204 No Content
```

### Opción 2: cURL

```bash
curl -X PUT https://localhost/api/v1/gyms/{GYM_ID}/students/{STUDENT_ID}/deactivate \
  -H "Authorization: Bearer {TRAINER_TOKEN}" \
  -H "Content-Type: application/json"

# ✅ Respuesta esperada: 204 (sin cuerpo)
```

### Opción 3: Frontend

1. Login como trainer
2. Ir a "Alumnos"
3. Seleccionar un alumno activo
4. Click en "Desactivar"
5. ✅ Alumno debe aparecer como inactivo

---

## 📋 Comparativa con endpoint similar

| Aspecto | `deactivate` | `reactivate` |
|---------|--------------|--------------|
| **Método HTTP** | PUT | PUT |
| **URL** | `.../deactivate` | `.../reactivate` |
| **Request Body** | ❌ Ninguno | ✅ `{quota_expires_at}` |
| **Response** | 204 No Content | 200 OK + data |
| **UseCase** | `DeactivateStudentUseCase` | `ReactivateStudentUseCase` |
| **Cache invalidation** | ✅ Automático (repository) | ✅ Automático (repository) |

---

## 🔍 Impacto del fix

### ✅ Funcionalidades restauradas:

1. **Colección Postman TASK_027**
   - Sección "3. CACHE - Pruebas de rendimiento"
   - Petición 3.3 ahora funciona ✅

2. **Tests de cache**
   - `test_student_only_sees_routines_from_active_gyms` ✅
   - Invalidación de cache funciona correctamente

3. **Frontend**
   - Botón "Desactivar alumno" funciona ✅
   - Cache de rutinas se invalida automáticamente

---

## 📝 Archivos modificados

1. ✅ `app/Infrastructure/Http/Controllers/V1/GymStudentController.php` (método agregado)
2. ✅ `routes/api_v1.php` (ruta agregada)

---

## 🎯 Endpoints de gestión de alumnos completos

| Endpoint | Método | Descripción | Estado |
|----------|--------|-------------|--------|
| `/gyms/{id}/students` | GET | Listar alumnos | ✅ |
| `/gyms/{id}/students` | POST | Matricular alumno | ✅ |
| `/gyms/{id}/students/{sid}` | PUT | Actualizar cuota | ✅ |
| `/gyms/{id}/students/{sid}` | DELETE | Eliminar matrícula | ✅ |
| `/gyms/{id}/students/{sid}/deactivate` | PUT | Desactivar alumno | ✅ **NUEVO** |
| `/gyms/{id}/students/{sid}/reactivate` | PUT | Reactivar alumno | ✅ |

---

## ✨ Nota sobre cache

La desactivación de un alumno **automáticamente invalida** su cache de rutinas gracias a:

1. `GymStudentEloquentRepository::save()` (línea 29-35)
   - Llama a `$cacheService->invalidate($studentId)`

2. `RoutineAssignmentCacheService::invalidate()` (líneas 62-92)
   - Compatible con drivers: `file`, `redis`, `array`
   - Elimina todas las keys del patrón `student:routines:{studentId}:*`

**Resultado:** Cuando un alumno se desactiva, al consultar sus rutinas verá una lista vacía (query filtra por `is_active=true` en `gym_students`).
