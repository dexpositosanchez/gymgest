<?php

declare(strict_types=1);

namespace App\Domain\WorkoutSession\Repositories;

use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;

interface WorkoutSessionExerciseStatusRepositoryInterface
{
    /**
     * Mark an exercise as completed in a workout session
     *
     * @param WorkoutSessionId $sessionId
     * @param ExerciseId $exerciseId
     * @return void
     */
    public function markAsCompleted(WorkoutSessionId $sessionId, ExerciseId $exerciseId): void;

    /**
     * Check if an exercise is marked as completed in a session
     *
     * @param WorkoutSessionId $sessionId
     * @param ExerciseId $exerciseId
     * @return bool
     */
    public function isExerciseCompleted(WorkoutSessionId $sessionId, ExerciseId $exerciseId): bool;
}
