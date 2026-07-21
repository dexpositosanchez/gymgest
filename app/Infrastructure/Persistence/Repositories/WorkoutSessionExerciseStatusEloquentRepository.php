<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionExerciseStatusRepositoryInterface;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Infrastructure\Persistence\Eloquent\WorkoutSessionExerciseStatusEloquentModel;
use Ramsey\Uuid\Uuid;

class WorkoutSessionExerciseStatusEloquentRepository implements WorkoutSessionExerciseStatusRepositoryInterface
{
    public function markAsCompleted(WorkoutSessionId $sessionId, ExerciseId $exerciseId): void
    {
        $existing = WorkoutSessionExerciseStatusEloquentModel::where('workout_session_id', $sessionId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->first();

        if ($existing) {
            // Update existing record
            $existing->is_completed = true;
            $existing->completed_at = now();
            $existing->save();
        } else {
            // Create new record
            WorkoutSessionExerciseStatusEloquentModel::create([
                'id' => Uuid::uuid4()->toString(),
                'workout_session_id' => $sessionId->getValue(),
                'exercise_id' => $exerciseId->getValue(),
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }
    }

    public function isExerciseCompleted(WorkoutSessionId $sessionId, ExerciseId $exerciseId): bool
    {
        return WorkoutSessionExerciseStatusEloquentModel::where('workout_session_id', $sessionId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->where('is_completed', true)
            ->exists();
    }
}
