<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\V1;

use App\Application\Statistics\UseCases\GetStudentRoutineStatsUseCase;
use App\Application\Statistics\UseCases\GetExerciseWeightHistoryUseCase;
use App\Application\Statistics\UseCases\GetGymActiveStudentsUseCase;
use App\Application\Statistics\UseCases\GetGymActiveStudentsCountUseCase;
use App\Application\Statistics\UseCases\GetStudentExecutedExercisesUseCase;
use App\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StatisticsController extends Controller
{
    private GetStudentRoutineStatsUseCase $getStudentRoutineStatsUseCase;
    private GetExerciseWeightHistoryUseCase $getExerciseWeightHistoryUseCase;
    private GetGymActiveStudentsUseCase $getGymActiveStudentsUseCase;
    private GetGymActiveStudentsCountUseCase $getGymActiveStudentsCountUseCase;
    private GetStudentExecutedExercisesUseCase $getStudentExecutedExercisesUseCase;

    public function __construct(
        GetStudentRoutineStatsUseCase $getStudentRoutineStatsUseCase,
        GetExerciseWeightHistoryUseCase $getExerciseWeightHistoryUseCase,
        GetGymActiveStudentsUseCase $getGymActiveStudentsUseCase,
        GetGymActiveStudentsCountUseCase $getGymActiveStudentsCountUseCase,
        GetStudentExecutedExercisesUseCase $getStudentExecutedExercisesUseCase
    ) {
        $this->getStudentRoutineStatsUseCase = $getStudentRoutineStatsUseCase;
        $this->getExerciseWeightHistoryUseCase = $getExerciseWeightHistoryUseCase;
        $this->getGymActiveStudentsUseCase = $getGymActiveStudentsUseCase;
        $this->getGymActiveStudentsCountUseCase = $getGymActiveStudentsCountUseCase;
        $this->getStudentExecutedExercisesUseCase = $getStudentExecutedExercisesUseCase;
    }

    /**
     * Trainer endpoint: Get routine stats for a specific student
     */
    public function studentRoutineStats(string $studentId): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;

            // Verify student belongs to trainer's gym
            $studentBelongsToTrainer = \DB::table('gym_students as gs')
                ->join('gyms as g', 'gs.gym_id', '=', 'g.id')
                ->where('gs.student_id', $studentId)
                ->where('g.trainer_id', $trainerId)
                ->exists();

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
     */
    public function studentExerciseWeightHistory(string $studentId, Request $request): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;

            // Verify student belongs to trainer's gym
            $studentBelongsToTrainer = \DB::table('gym_students as gs')
                ->join('gyms as g', 'gs.gym_id', '=', 'g.id')
                ->where('gs.student_id', $studentId)
                ->where('g.trainer_id', $trainerId)
                ->exists();

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
     */
    public function studentExecutedExercises(string $studentId): JsonResponse
    {
        try {
            $trainerId = auth()->user()->id;

            // Verify student belongs to trainer's gym
            $studentBelongsToTrainer = \DB::table('gym_students as gs')
                ->join('gyms as g', 'gs.gym_id', '=', 'g.id')
                ->where('gs.student_id', $studentId)
                ->where('g.trainer_id', $trainerId)
                ->exists();

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
