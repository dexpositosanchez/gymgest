<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\Statistics\UseCases\GetStudentRoutineStatsUseCase;
use App\Application\Statistics\UseCases\GetExerciseWeightHistoryUseCase;
use App\Application\Statistics\UseCases\GetGymActiveStudentsUseCase;
use App\Application\Statistics\UseCases\GetGymActiveStudentsCountUseCase;
use App\Application\Statistics\UseCases\GetStudentExecutedExercisesUseCase;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * @OA\Tag(
 *     name="Statistics",
 *     description="Statistics and analytics endpoints for trainers and students"
 * )
 */
class StatisticsController extends Controller
{
    private GetStudentRoutineStatsUseCase $getStudentRoutineStatsUseCase;
    private GetExerciseWeightHistoryUseCase $getExerciseWeightHistoryUseCase;
    private GetGymActiveStudentsUseCase $getGymActiveStudentsUseCase;
    private GetGymActiveStudentsCountUseCase $getGymActiveStudentsCountUseCase;
    private GetStudentExecutedExercisesUseCase $getStudentExecutedExercisesUseCase;
    private GymStudentRepositoryInterface $gymStudentRepository;

    public function __construct(
        GetStudentRoutineStatsUseCase $getStudentRoutineStatsUseCase,
        GetExerciseWeightHistoryUseCase $getExerciseWeightHistoryUseCase,
        GetGymActiveStudentsUseCase $getGymActiveStudentsUseCase,
        GetGymActiveStudentsCountUseCase $getGymActiveStudentsCountUseCase,
        GetStudentExecutedExercisesUseCase $getStudentExecutedExercisesUseCase,
        GymStudentRepositoryInterface $gymStudentRepository
    ) {
        $this->getStudentRoutineStatsUseCase = $getStudentRoutineStatsUseCase;
        $this->getExerciseWeightHistoryUseCase = $getExerciseWeightHistoryUseCase;
        $this->getGymActiveStudentsUseCase = $getGymActiveStudentsUseCase;
        $this->getGymActiveStudentsCountUseCase = $getGymActiveStudentsCountUseCase;
        $this->getStudentExecutedExercisesUseCase = $getStudentExecutedExercisesUseCase;
        $this->gymStudentRepository = $gymStudentRepository;
    }

    /**
     * Trainer endpoint: Get routine stats for a specific student
     *
     * @OA\Get(
     *     path="/students/{studentId}/statistics/routines",
     *     summary="Get routine execution statistics for a student (Trainer only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Routine execution statistics",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="routine_id", type="string", format="uuid"),
     *                     @OA\Property(property="routine_name", type="string"),
     *                     @OA\Property(property="times_executed", type="integer"),
     *                     @OA\Property(property="first_session_at", type="string", format="date-time"),
     *                     @OA\Property(property="last_session_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Student does not belong to trainer"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function studentRoutineStats(string $studentId): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;

            // Verify student belongs to trainer's gym
            $studentBelongsToTrainer = $this->gymStudentRepository->studentBelongsToTrainer(
                new UserId($studentId),
                new UserId($trainerId)
            );

            if (!$studentBelongsToTrainer) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $stats = $this->getStudentRoutineStatsUseCase->execute($studentId);
            return response()->json(['data' => $stats]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Student endpoint: Get own routine stats
     *
     * @OA\Get(
     *     path="/students/me/statistics/routines",
     *     summary="Get own routine execution statistics (Student only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Routine execution statistics",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="routine_id", type="string", format="uuid"),
     *                     @OA\Property(property="routine_name", type="string"),
     *                     @OA\Property(property="times_executed", type="integer"),
     *                     @OA\Property(property="first_session_at", type="string", format="date-time"),
     *                     @OA\Property(property="last_session_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function myRoutineStats(): JsonResponse
    {
        try {
            $studentId = auth()->user()->id;
            $stats = $this->getStudentRoutineStatsUseCase->execute($studentId);
            return response()->json(['data' => $stats]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Trainer endpoint: Get exercise weight history for a specific student
     *
     * @OA\Get(
     *     path="/students/{studentId}/statistics/exercise-weight-history",
     *     summary="Get weight progression history for a student's exercise (Trainer only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="exercise_id",
     *         in="query",
     *         required=true,
     *         description="Exercise ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="reps",
     *         in="query",
     *         required=true,
     *         description="Number of reps",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Weight history progression",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date"),
     *                     @OA\Property(property="weight", type="number", format="float"),
     *                     @OA\Property(property="reps", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Student does not belong to trainer"),
     *     @OA\Response(response=422, description="Validation error - Missing required parameters")
     * )
     */
    public function studentExerciseWeightHistory(string $studentId, Request $request): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;

            // Verify student belongs to trainer's gym
            $studentBelongsToTrainer = $this->gymStudentRepository->studentBelongsToTrainer(
                new UserId($studentId),
                new UserId($trainerId)
            );

            if (!$studentBelongsToTrainer) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Validate required parameters
            $exerciseId = $request->query('exercise_id');
            $reps = $request->query('reps');

            if (!$exerciseId || !$reps) {
                return response()->json(['error' => 'exercise_id and reps are required'], 422);
            }

            $history = $this->getExerciseWeightHistoryUseCase->execute(
                $studentId,
                $exerciseId,
                (int) $reps
            );
            return response()->json(['data' => $history]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Student endpoint: Get own exercise weight history
     *
     * @OA\Get(
     *     path="/students/me/statistics/exercise-weight-history",
     *     summary="Get own weight progression history for an exercise (Student only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="exercise_id",
     *         in="query",
     *         required=true,
     *         description="Exercise ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="reps",
     *         in="query",
     *         required=true,
     *         description="Number of reps",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Weight history progression",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="date", type="string", format="date"),
     *                     @OA\Property(property="weight", type="number", format="float"),
     *                     @OA\Property(property="reps", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error - Missing required parameters")
     * )
     */
    public function myExerciseWeightHistory(Request $request): JsonResponse
    {
        try {
            $studentId = auth()->user()->id;

            // Validate required parameters
            $exerciseId = $request->query('exercise_id');
            $reps = $request->query('reps');

            if (!$exerciseId || !$reps) {
                return response()->json(['error' => 'exercise_id and reps are required'], 422);
            }

            $history = $this->getExerciseWeightHistoryUseCase->execute(
                $studentId,
                $exerciseId,
                (int) $reps
            );
            return response()->json(['data' => $history]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Trainer endpoint: Get gym active students with full details
     *
     * @OA\Get(
     *     path="/gyms/{gymId}/statistics/active-students",
     *     summary="Get list of active students in a gym (Trainer only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="gymId",
     *         in="path",
     *         required=true,
     *         description="Gym ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active students with workout details",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="student_id", type="string", format="uuid"),
     *                     @OA\Property(property="student_name", type="string"),
     *                     @OA\Property(property="last_workout_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Not authorized to access this gym"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function gymActiveStudents(string $gymId): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;
            $stats = $this->getGymActiveStudentsUseCase->execute($gymId, $trainerId);
            return response()->json(['data' => $stats]);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Unauthorized') {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Student endpoint: Get gym active students count only (privacy)
     *
     * @OA\Get(
     *     path="/students/me/gyms/{gymId}/statistics/active-students",
     *     summary="Get count of active students in gym (Student only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="gymId",
     *         in="path",
     *         required=true,
     *         description="Gym ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Total count of active students",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_active_students", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Not enrolled in this gym"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function myGymActiveStudents(string $gymId): JsonResponse
    {
        try {
            $studentId = auth()->user()->id;
            $count = $this->getGymActiveStudentsCountUseCase->execute($gymId, $studentId);
            return response()->json(['total_active_students' => $count]);
        } catch (InvalidArgumentException $e) {
            if ($e->getMessage() === 'Unauthorized') {
                return response()->json(['error' => $e->getMessage()], 403);
            }
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Trainer endpoint: Get executed exercises for a specific student
     *
     * @OA\Get(
     *     path="/students/{studentId}/statistics/exercises-executed",
     *     summary="Get list of all exercises executed by a student (Trainer only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of executed exercises with details",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="exercise_id", type="string", format="uuid"),
     *                     @OA\Property(property="exercise_name", type="string"),
     *                     @OA\Property(property="muscle_group", type="string"),
     *                     @OA\Property(property="unique_reps", type="array", @OA\Items(type="integer")),
     *                     @OA\Property(property="total_executions", type="integer"),
     *                     @OA\Property(property="first_executed_at", type="string", format="date-time"),
     *                     @OA\Property(property="last_executed_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - Student does not belong to trainer"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function studentExecutedExercises(string $studentId): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;

            // Verify student belongs to trainer's gym
            $studentBelongsToTrainer = $this->gymStudentRepository->studentBelongsToTrainer(
                new UserId($studentId),
                new UserId($trainerId)
            );

            if (!$studentBelongsToTrainer) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $exercises = $this->getStudentExecutedExercisesUseCase->execute($studentId);
            return response()->json(['data' => $exercises]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Student endpoint: Get own executed exercises
     *
     * @OA\Get(
     *     path="/students/me/statistics/exercises-executed",
     *     summary="Get list of all exercises executed by student (Student only)",
     *     tags={"Statistics"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of executed exercises with details",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="exercise_id", type="string", format="uuid"),
     *                     @OA\Property(property="exercise_name", type="string"),
     *                     @OA\Property(property="muscle_group", type="string"),
     *                     @OA\Property(property="unique_reps", type="array", @OA\Items(type="integer")),
     *                     @OA\Property(property="total_executions", type="integer"),
     *                     @OA\Property(property="first_executed_at", type="string", format="date-time"),
     *                     @OA\Property(property="last_executed_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function myExecutedExercises(): JsonResponse
    {
        try {
            $studentId = auth()->user()->id;
            $exercises = $this->getStudentExecutedExercisesUseCase->execute($studentId);
            return response()->json(['data' => $exercises]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
