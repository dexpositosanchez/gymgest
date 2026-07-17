<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\WorkoutSession\UseCases\StartWorkoutSessionUseCase;
use App\Application\WorkoutSession\UseCases\GetActiveWorkoutSessionUseCase;
use App\Application\WorkoutSession\UseCases\FinishWorkoutSessionUseCase;
use App\Application\WorkoutSession\UseCases\GetWorkoutHistoryUseCase;
use App\Application\WorkoutSession\UseCases\MarkExerciseCompleteUseCase;
use App\Application\SetExecution\UseCases\ExecuteSetUseCase;
use App\Application\SetExecution\UseCases\GetExerciseSetsUseCase;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\StartWorkoutSessionRequest;
use App\Infrastructure\Http\Requests\ExecuteSetRequest;
use App\Infrastructure\Http\Requests\FinishWorkoutSessionRequest;
use App\Infrastructure\Persistence\Eloquent\WorkoutSessionEloquentModel;
use App\Infrastructure\Persistence\Eloquent\SetExecutionEloquentModel;
use App\Infrastructure\Persistence\Eloquent\WorkoutSessionExerciseStatusEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineAssignmentEloquentModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Student Workout Sessions",
 *     description="Student workout session management endpoints"
 * )
 */
class WorkoutSessionController extends Controller
{
    /** @var StartWorkoutSessionUseCase */
    private $startWorkoutSessionUseCase;

    /** @var GetActiveWorkoutSessionUseCase */
    private $getActiveWorkoutSessionUseCase;

    /** @var FinishWorkoutSessionUseCase */
    private $finishWorkoutSessionUseCase;

    /** @var GetWorkoutHistoryUseCase */
    private $getWorkoutHistoryUseCase;

    /** @var ExecuteSetUseCase */
    private $executeSetUseCase;

    /** @var GetExerciseSetsUseCase */
    private $getExerciseSetsUseCase;

    /** @var MarkExerciseCompleteUseCase */
    private $markExerciseCompleteUseCase;

    public function __construct(
        StartWorkoutSessionUseCase $startWorkoutSessionUseCase,
        GetActiveWorkoutSessionUseCase $getActiveWorkoutSessionUseCase,
        FinishWorkoutSessionUseCase $finishWorkoutSessionUseCase,
        GetWorkoutHistoryUseCase $getWorkoutHistoryUseCase,
        ExecuteSetUseCase $executeSetUseCase,
        GetExerciseSetsUseCase $getExerciseSetsUseCase,
        MarkExerciseCompleteUseCase $markExerciseCompleteUseCase
    ) {
        $this->startWorkoutSessionUseCase = $startWorkoutSessionUseCase;
        $this->getActiveWorkoutSessionUseCase = $getActiveWorkoutSessionUseCase;
        $this->finishWorkoutSessionUseCase = $finishWorkoutSessionUseCase;
        $this->getWorkoutHistoryUseCase = $getWorkoutHistoryUseCase;
        $this->executeSetUseCase = $executeSetUseCase;
        $this->getExerciseSetsUseCase = $getExerciseSetsUseCase;
        $this->markExerciseCompleteUseCase = $markExerciseCompleteUseCase;
        $this->middleware('jwt.auth');
    }

    /**
     * @OA\Post(
     *     path="/students/me/workout-sessions",
     *     summary="Start a new workout session",
     *     tags={"Student Workout Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"routine_assignment_id", "day_number"},
     *             @OA\Property(property="routine_assignment_id", type="string", format="uuid"),
     *             @OA\Property(property="day_number", type="integer", minimum=1),
     *             @OA\Property(property="notes", type="string", maxLength=500, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Workout session started",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="routine_assignment_id", type="string", format="uuid"),
     *             @OA\Property(property="day_number", type="integer"),
     *             @OA\Property(property="started_at", type="string", format="date-time"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Student already has active session"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=403, description="Only students can access this endpoint")
     * )
     */
    public function start(StartWorkoutSessionRequest $request): JsonResponse
    {
        try {
            // Guard: Only students can start workout sessions
            if (auth()->user()->user_type !== 'student') {
                return response()->json(['error' => 'This endpoint is only for students'], 403);
            }

            $session = $this->startWorkoutSessionUseCase->execute(
                auth()->user()->id,
                $request->input('routine_assignment_id'),
                $request->input('day_number'),
                $request->input('notes')
            );

            return response()->json([
                'id' => $session->getId()->getValue(),
                'routine_assignment_id' => $session->getRoutineAssignmentId()->getValue(),
                'day_number' => $session->getDayNumber()->getValue(),
                'started_at' => $session->getStartedAt()->format('Y-m-d H:i:s'),
                'is_active' => $session->isActive(),
            ], 201);

        } catch (\DomainException $e) {
            if ($e->getMessage() === 'Ya tienes una sesión activa') {
                return response()->json(['error' => $e->getMessage()], 409);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al iniciar sesión de entrenamiento'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/students/me/workout-sessions/active",
     *     summary="Get active workout session with exercises",
     *     tags={"Student Workout Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Active workout session",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="day_number", type="integer"),
     *             @OA\Property(property="started_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="exercises",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="exercise_id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="total_sets", type="integer"),
     *                     @OA\Property(property="completed_sets", type="integer"),
     *                     @OA\Property(property="is_completed", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="No active session"),
     *     @OA\Response(response=403, description="Only students can access this endpoint")
     * )
     */
    public function getActive(Request $request): JsonResponse
    {
        try {
            // Guard: Only students can access this endpoint
            if (auth()->user()->user_type !== 'student') {
                return response()->json(['error' => 'This endpoint is only for students'], 403);
            }

            $session = $this->getActiveWorkoutSessionUseCase->execute(auth()->user()->id);

            if ($session === null) {
                return response()->json(['error' => 'No tienes una sesión activa'], 404);
            }

            // Get exercises for this session
            $assignment = RoutineAssignmentEloquentModel::with([
                'routine.days.exercises.exercise',
                'routine.days.exercises.sets'
            ])->find($session->getRoutineAssignmentId()->getValue());

            $day = $assignment->routine->days->firstWhere('day_number', $session->getDayNumber()->getValue());

            $exercises = [];
            foreach ($day->exercises as $routineDayExercise) {
                $completedSets = SetExecutionEloquentModel::where('workout_session_id', $session->getId()->getValue())
                    ->where('exercise_id', $routineDayExercise->exercise_id)
                    ->count();

                $isCompleted = WorkoutSessionExerciseStatusEloquentModel::where('workout_session_id', $session->getId()->getValue())
                    ->where('exercise_id', $routineDayExercise->exercise_id)
                    ->where('is_completed', true)
                    ->exists();

                $exercises[] = [
                    'exercise_id' => $routineDayExercise->exercise_id,
                    'name' => $routineDayExercise->exercise->name,
                    'total_sets' => $routineDayExercise->sets->count(),
                    'completed_sets' => $completedSets,
                    'is_completed' => $isCompleted,
                ];
            }

            return response()->json([
                'id' => $session->getId()->getValue(),
                'day_number' => $session->getDayNumber()->getValue(),
                'started_at' => $session->getStartedAt()->format('Y-m-d H:i:s'),
                'exercises' => $exercises,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener sesión activa'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/students/me/workout-sessions/{sessionId}/exercises/{exerciseId}/sets",
     *     summary="Get sets for an exercise in a session",
     *     tags={"Student Workout Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="exerciseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exercise sets",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="sets",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="set_number", type="integer"),
     *                     @OA\Property(property="min_reps", type="integer"),
     *                     @OA\Property(property="max_reps", type="integer"),
     *                     @OA\Property(property="rest_seconds", type="integer"),
     *                     @OA\Property(property="suggested_weight", type="number", format="float", nullable=true),
     *                     @OA\Property(property="is_completed", type="boolean")
     *                 )
     *             ),
     *             @OA\Property(property="total_sets", type="integer"),
     *             @OA\Property(property="completed_sets", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Only students can access this endpoint")
     * )
     */
    public function getSets(string $sessionId, string $exerciseId): JsonResponse
    {
        try {
            // Guard: Only students can access this endpoint
            if (auth()->user()->user_type !== 'student') {
                return response()->json(['error' => 'This endpoint is only for students'], 403);
            }

            $result = $this->getExerciseSetsUseCase->execute($sessionId, $exerciseId, auth()->user()->id);

            return response()->json($result, 200);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener series del ejercicio'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/students/me/workout-sessions/{sessionId}/exercises/{exerciseId}/sets",
     *     summary="Execute a set",
     *     tags={"Student Workout Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="exerciseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"set_number", "reps_completed"},
     *             @OA\Property(property="set_number", type="integer", minimum=1),
     *             @OA\Property(property="reps_completed", type="integer", minimum=1, maximum=999),
     *             @OA\Property(property="weight_used", type="number", format="float", minimum=0, maximum=999.99, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Set executed successfully"
     *     ),
     *     @OA\Response(response=422, description="Validation error or session finished"),
     *     @OA\Response(response=403, description="Only students can access this endpoint")
     * )
     */
    public function executeSet(ExecuteSetRequest $request, string $sessionId, string $exerciseId): JsonResponse
    {
        try {
            // Guard: Only students can access this endpoint
            if (auth()->user()->user_type !== 'student') {
                return response()->json(['error' => 'This endpoint is only for students'], 403);
            }

            $this->executeSetUseCase->execute(
                $sessionId,
                $exerciseId,
                $request->input('set_number'),
                $request->input('reps_completed'),
                $request->input('weight_used')
            );

            return response()->json(['message' => 'Serie ejecutada exitosamente'], 201);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al ejecutar serie'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/students/me/workout-sessions/{sessionId}/exercises/{exerciseId}/mark-complete",
     *     summary="Mark exercise as complete manually",
     *     tags={"Student Workout Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="exerciseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=204, description="Exercise marked as complete"),
     *     @OA\Response(response=403, description="Only students can access this endpoint")
     * )
     */
    public function markExerciseComplete(string $sessionId, string $exerciseId): JsonResponse
    {
        try {
            // Guard: Only students can access this endpoint
            if (auth()->user()->user_type !== 'student') {
                return response()->json(['error' => 'This endpoint is only for students'], 403);
            }

            $this->markExerciseCompleteUseCase->execute($sessionId, $exerciseId, auth()->user()->id);

            return response()->json(null, 204);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al marcar ejercicio como completado'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/students/me/workout-sessions/{sessionId}/finish",
     *     summary="Finish workout session",
     *     tags={"Student Workout Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sessionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="notes", type="string", maxLength=500, nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=204, description="Session finished successfully"),
     *     @OA\Response(response=422, description="Session already finished or not found"),
     *     @OA\Response(response=403, description="Only students can access this endpoint")
     * )
     */
    public function finish(FinishWorkoutSessionRequest $request, string $sessionId): JsonResponse
    {
        try {
            // Guard: Only students can access this endpoint
            if (auth()->user()->user_type !== 'student') {
                return response()->json(['error' => 'This endpoint is only for students'], 403);
            }

            $this->finishWorkoutSessionUseCase->execute(
                $sessionId,
                auth()->user()->id,
                $request->input('notes')
            );

            return response()->json(null, 204);

        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al finalizar sesión'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/students/me/workout-sessions",
     *     summary="Get workout session history",
     *     tags={"Student Workout Sessions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Workout session history",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="day_number", type="integer"),
     *                     @OA\Property(property="started_at", type="string", format="date-time"),
     *                     @OA\Property(property="finished_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Only students can access this endpoint")
     * )
     */
    public function history(Request $request): JsonResponse
    {
        try {
            // Guard: Only students can access this endpoint
            if (auth()->user()->user_type !== 'student') {
                return response()->json(['error' => 'This endpoint is only for students'], 403);
            }

            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 15);

            $result = $this->getWorkoutHistoryUseCase->execute(auth()->user()->id, $page, $perPage);

            $data = array_map(function ($session) {
                return [
                    'id' => $session->getId()->getValue(),
                    'day_number' => $session->getDayNumber()->getValue(),
                    'started_at' => $session->getStartedAt()->format('Y-m-d H:i:s'),
                    'finished_at' => $session->getFinishedAt() ? $session->getFinishedAt()->format('Y-m-d H:i:s') : null,
                ];
            }, $result['data']);

            return response()->json([
                'data' => $data,
                'meta' => [
                    'current_page' => $result['current_page'],
                    'per_page' => $result['per_page'],
                    'total' => $result['total'],
                    'last_page' => $result['last_page'],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener historial de sesiones'], 500);
        }
    }
}
