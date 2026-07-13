<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\RoutineAssignment\DTOs\AssignRoutineDTO;
use App\Application\RoutineAssignment\DTOs\UpdateAssignmentDTO;
use App\Application\RoutineAssignment\UseCases\AssignRoutineUseCase;
use App\Application\RoutineAssignment\UseCases\DeleteAssignmentUseCase;
use App\Application\RoutineAssignment\UseCases\ListTrainerStudentRoutinesUseCase;
use App\Application\RoutineAssignment\UseCases\SetCurrentRoutineUseCase;
use App\Application\RoutineAssignment\UseCases\UpdateAssignmentUseCase;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\AssignRoutineRequest;
use App\Infrastructure\Http\Requests\UpdateAssignmentRequest;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

/**
 * @OA\Tag(
 *     name="Routine Assignments",
 *     description="Gestión de asignación de rutinas a alumnos"
 * )
 */
class RoutineAssignmentController extends Controller
{
    private ListTrainerStudentRoutinesUseCase $listTrainerStudentRoutinesUseCase;
    private AssignRoutineUseCase $assignRoutineUseCase;
    private UpdateAssignmentUseCase $updateAssignmentUseCase;
    private DeleteAssignmentUseCase $deleteAssignmentUseCase;
    private SetCurrentRoutineUseCase $setCurrentRoutineUseCase;

    public function __construct(
        ListTrainerStudentRoutinesUseCase $listTrainerStudentRoutinesUseCase,
        AssignRoutineUseCase $assignRoutineUseCase,
        UpdateAssignmentUseCase $updateAssignmentUseCase,
        DeleteAssignmentUseCase $deleteAssignmentUseCase,
        SetCurrentRoutineUseCase $setCurrentRoutineUseCase
    ) {
        $this->listTrainerStudentRoutinesUseCase = $listTrainerStudentRoutinesUseCase;
        $this->assignRoutineUseCase = $assignRoutineUseCase;
        $this->updateAssignmentUseCase = $updateAssignmentUseCase;
        $this->deleteAssignmentUseCase = $deleteAssignmentUseCase;
        $this->setCurrentRoutineUseCase = $setCurrentRoutineUseCase;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/gyms/{gymId}/students/{studentId}/routines",
     *     summary="Listar rutinas asignadas a un alumno en un gimnasio",
     *     tags={"Routine Assignments"},
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
     *     @OA\Response(response=200, description="Lista de rutinas asignadas"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado")
     * )
     */
    public function index(string $gymId, string $studentId): JsonResponse
    {
        try {
            $assignments = $this->listTrainerStudentRoutinesUseCase->execute($studentId, $gymId);

            return response()->json(['data' => $assignments], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/gyms/{gymId}/students/{studentId}/routines",
     *     summary="Asignar rutina a un alumno",
     *     tags={"Routine Assignments"},
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
     *             @OA\Property(property="routine_id", type="string", format="uuid"),
     *             @OA\Property(property="starts_at", type="string", format="date", example="2026-07-15"),
     *             @OA\Property(property="is_current", type="boolean", example=true),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Rutina asignada exitosamente"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function store(AssignRoutineRequest $request, string $gymId, string $studentId): JsonResponse
    {
        try {
            $dto = new AssignRoutineDTO(
                $request->input('routine_id'),
                $studentId,
                $gymId,
                $request->input('starts_at'),
                $request->input('is_current', true),
                $request->input('notes')
            );

            $assignment = $this->assignRoutineUseCase->execute($dto);

            return response()->json(['data' => $assignment], 201);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Gym not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            if ($e->getMessage() === 'Routine not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            if ($e->getMessage() === 'Unauthorized' ||
                strpos($e->getMessage(), 'does not belong') !== false ||
                strpos($e->getMessage(), 'not active') !== false ||
                strpos($e->getMessage(), 'not enrolled') !== false) {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/gyms/{gymId}/students/{studentId}/routines/{assignmentId}",
     *     summary="Actualizar asignación de rutina",
     *     tags={"Routine Assignments"},
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
     *     @OA\Parameter(
     *         name="assignmentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="starts_at", type="string", format="date", nullable=true),
     *             @OA\Property(property="is_current", type="boolean", nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Asignación actualizada"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Asignación no encontrada"),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function update(UpdateAssignmentRequest $request, string $gymId, string $studentId, string $assignmentId): JsonResponse
    {
        try {
            $trainerId = auth()->id();

            $dto = new UpdateAssignmentDTO(
                $request->input('starts_at'),
                $request->input('is_current'),
                $request->input('notes')
            );

            $assignment = $this->updateAssignmentUseCase->execute($assignmentId, $dto, $trainerId);

            return response()->json(['data' => $assignment], 200);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Assignment not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
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
     * @OA\Delete(
     *     path="/api/v1/gyms/{gymId}/students/{studentId}/routines/{assignmentId}",
     *     summary="Eliminar asignación de rutina",
     *     tags={"Routine Assignments"},
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
     *     @OA\Parameter(
     *         name="assignmentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=204, description="Asignación eliminada"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Asignación no encontrada")
     * )
     */
    public function destroy(string $gymId, string $studentId, string $assignmentId): JsonResponse
    {
        try {
            $trainerId = auth()->id();

            $this->deleteAssignmentUseCase->execute($assignmentId, $trainerId);

            return response()->json(null, 204);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Assignment not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
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
     * @OA\Put(
     *     path="/api/v1/gyms/{gymId}/students/{studentId}/routines/{assignmentId}/set-current",
     *     summary="Marcar rutina como actual",
     *     tags={"Routine Assignments"},
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
     *     @OA\Parameter(
     *         name="assignmentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Rutina marcada como actual"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Asignación no encontrada")
     * )
     */
    public function setCurrent(string $gymId, string $studentId, string $assignmentId): JsonResponse
    {
        try {
            $trainerId = auth()->id();

            $this->setCurrentRoutineUseCase->execute($assignmentId, $trainerId);

            return response()->json(['message' => 'Routine set as current'], 200);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Assignment not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            if ($e->getMessage() === 'Gym not found') {
                return response()->json(['error' => $e->getMessage()], 404);
            }
            if ($e->getMessage() === 'Unauthorized') {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
