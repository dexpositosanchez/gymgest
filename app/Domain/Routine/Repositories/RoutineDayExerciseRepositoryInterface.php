<?php

declare(strict_types=1);

namespace App\Domain\Routine\Repositories;

use App\Domain\Routine\Entities\RoutineDayExerciseEntity;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\DayNumber;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;

interface RoutineDayExerciseRepositoryInterface
{
    public function findBySessionAndExercise(
        WorkoutSessionId $sessionId,
        ExerciseId $exerciseId
    ): ?RoutineDayExerciseEntity;

    /**
     * Get exercises with details for a routine day (pragmatic approach for performance)
     * Returns array with exercise_id, exercise_name, total_sets
     *
     * @param RoutineId $routineId
     * @param DayNumber $dayNumber
     * @return array Array of exercises with [exercise_id, exercise_name, total_sets]
     */
    public function getExercisesWithDetailsForDay(RoutineId $routineId, DayNumber $dayNumber): array;
}
