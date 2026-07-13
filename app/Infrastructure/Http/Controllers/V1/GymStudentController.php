<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\GymStudent\DTOs\EnrollStudentDTO;
use App\Application\GymStudent\UseCases\DeactivateStudentUseCase;
use App\Application\GymStudent\UseCases\EnrollStudentUseCase;
use App\Application\GymStudent\UseCases\ListAllStudentsUseCase;
use App\Application\GymStudent\UseCases\ListGymStudentsUseCase;
use App\Application\GymStudent\UseCases\ReactivateStudentUseCase;
use App\Application\GymStudent\UseCases\UpdateStudentQuotaUseCase;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\EnrollStudentRequest;
use App\Infrastructure\Http\Requests\ReactivateStudentRequest;
use App\Infrastructure\Http\Requests\UpdateQuotaRequest;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

/**
 * @OA\Tag(
 *     name="Gym Students",
 *     description="Gestión de alumnos en gimnasios"
 * )
 */
class GymStudentController extends Controller
{
    private EnrollStudentUseCase $enrollStudentUseCase;
    private ListGymStudentsUseCase $listGymStudentsUseCase;
    private UpdateStudentQuotaUseCase $updateStudentQuotaUseCase;
    private DeactivateStudentUseCase $deactivateStudentUseCase;
    private ReactivateStudentUseCase $reactivateStudentUseCase;
    private ListAllStudentsUseCase $listAllStudentsUseCase;

    public function __construct(
        EnrollStudentUseCase $enrollStudentUseCase,
        ListGymStudentsUseCase $listGymStudentsUseCase,
        UpdateStudentQuotaUseCase $updateStudentQuotaUseCase,
        DeactivateStudentUseCase $deactivateStudentUseCase,
        ReactivateStudentUseCase $reactivateStudentUseCase,
        ListAllStudentsUseCase $listAllStudentsUseCase
    ) {
        $this->enrollStudentUseCase = $enrollStudentUseCase;
        $this->listGymStudentsUseCase = $listGymStudentsUseCase;
        $this->updateStudentQuotaUseCase = $updateStudentQuotaUseCase;
        $this->deactivateStudentUseCase = $deactivateStudentUseCase;
        $this->reactivateStudentUseCase = $reactivateStudentUseCase;
        $this->listAllStudentsUseCase = $listAllStudentsUseCase;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/gyms/{gymId}/students",
     *     summary="Listar alumnos de un gimnasio",
     *     tags={"Gym Students"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="gymId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Lista de alumnos"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Gimnasio no encontrado")
     * )
     */
    public function index(string $gymId): JsonResponse
    {
        try {
            $trainerId = auth()->id();
            $students = $this->listGymStudentsUseCase->execute($gymId, $trainerId);

            return response()->json(['data' => $students], 200);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Gym not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            if ($e->getMessage() === 'Unauthorized') {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/gyms/{gymId}/students",
     *     summary="Matricular alumno en un gimnasio",
     *     tags={"Gym Students"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="gymId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "quota_expires_at"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="quota_expires_at", type="string", format="date", example="2026-12-31")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Alumno matriculado"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Email no encontrado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(EnrollStudentRequest $request, string $gymId): JsonResponse
    {
        try {
            $trainerId = auth()->id();

            // Use gym_id from request body if provided, otherwise use path parameter
            // If gym_id is explicitly null in body, pass null (for personal training)
            $dto = new EnrollStudentDTO(
                $request->input('gym_id', $gymId),
                $request->input('email'),
                $request->input('quota_expires_at')
            );

            $student = $this->enrollStudentUseCase->execute($dto, $trainerId);

            return response()->json(['data' => $student], 201);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Gym not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            if ($e->getMessage() === 'Unauthorized') {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            if ($e->getMessage() === 'No existe ningún alumno registrado con ese email') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/personal-training/students",
     *     summary="Matricular alumno en entrenamiento personal",
     *     tags={"Gym Students"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "quota_expires_at"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="quota_expires_at", type="string", format="date", example="2026-12-31")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Alumno matriculado en entrenamiento personal"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=404, description="Email no encontrado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function personalTrainingEnroll(EnrollStudentRequest $request): JsonResponse
    {
        try {
            $trainerId = auth()->id();

            // gym_id is null for personal training - UseCase will create/get virtual gym
            $dto = new EnrollStudentDTO(
                null,
                $request->input('email'),
                $request->input('quota_expires_at')
            );

            $student = $this->enrollStudentUseCase->execute($dto, $trainerId);

            return response()->json(['data' => $student], 201);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Gym not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            if ($e->getMessage() === 'Unauthorized') {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            if ($e->getMessage() === 'No existe ningún alumno registrado con ese email') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/gyms/{gymId}/students/{studentId}",
     *     summary="Modificar cuota de un alumno",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quota_expires_at"},
     *             @OA\Property(property="quota_expires_at", type="string", format="date", example="2026-12-31")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Cuota actualizada"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Alumno no encontrado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpdateQuotaRequest $request, string $gymId, string $studentId): JsonResponse
    {
        try {
            $trainerId = auth()->id();
            $student = $this->updateStudentQuotaUseCase->execute(
                $gymId,
                $studentId,
                $request->input('quota_expires_at'),
                $trainerId
            );

            return response()->json(['data' => $student], 200);
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

    /**
     * @OA\Delete(
     *     path="/api/v1/gyms/{gymId}/students/{studentId}",
     *     summary="Dar de baja a un alumno (baja lógica)",
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
     *     @OA\Response(response=204, description="Alumno dado de baja"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Alumno no encontrado")
     * )
     */
    public function destroy(string $gymId, string $studentId): JsonResponse
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

    /**
     * @OA\Put(
     *     path="/api/v1/gyms/{gymId}/students/{studentId}/reactivate",
     *     summary="Dar de alta a un alumno inactivo",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quota_expires_at"},
     *             @OA\Property(property="quota_expires_at", type="string", format="date", example="2026-12-31")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Alumno reactivado"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Alumno no encontrado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function reactivate(ReactivateStudentRequest $request, string $gymId, string $studentId): JsonResponse
    {
        try {
            $trainerId = auth()->id();
            $student = $this->reactivateStudentUseCase->execute(
                $gymId,
                $studentId,
                $request->input('quota_expires_at'),
                $trainerId
            );

            return response()->json(['data' => $student], 200);
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

    /**
     * @OA\Get(
     *     path="/api/v1/students",
     *     summary="Listar todos los alumnos de todos los gimnasios del entrenador",
     *     tags={"Gym Students"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de todos los alumnos"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function listAll(): JsonResponse
    {
        $trainerId = auth()->id();
        $students = $this->listAllStudentsUseCase->execute($trainerId);

        return response()->json(['data' => $students], 200);
    }
}
