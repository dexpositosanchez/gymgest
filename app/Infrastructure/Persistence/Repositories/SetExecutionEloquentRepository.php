<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\SetExecution\Entities\SetExecutionEntity;
use App\Domain\SetExecution\Repositories\SetExecutionRepositoryInterface;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Infrastructure\Persistence\Eloquent\SetExecutionEloquentModel;
use App\Infrastructure\Persistence\Mappers\SetExecutionMapper;

class SetExecutionEloquentRepository implements SetExecutionRepositoryInterface
{
    public function save(SetExecutionEntity $setExecution): void
    {
        $model = SetExecutionMapper::toEloquent($setExecution);
        $model->save();
    }

    public function findBySessionId(WorkoutSessionId $sessionId): array
    {
        $models = SetExecutionEloquentModel::where('workout_session_id', $sessionId->getValue())
            ->orderBy('completed_at', 'asc')
            ->get();

        return $models->map(fn($model) => SetExecutionMapper::toDomain($model))->all();
    }

    public function findBySessionAndExercise(WorkoutSessionId $sessionId, ExerciseId $exerciseId): array
    {
        $models = SetExecutionEloquentModel::where('workout_session_id', $sessionId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->orderBy('set_number', 'asc')
            ->get();

        return $models->map(fn($model) => SetExecutionMapper::toDomain($model))->all();
    }

    public function countCompletedSets(WorkoutSessionId $sessionId, ExerciseId $exerciseId): int
    {
        return SetExecutionEloquentModel::where('workout_session_id', $sessionId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->count();
    }

    public function countBySessionAndExercise(WorkoutSessionId $sessionId, ExerciseId $exerciseId): int
    {
        return SetExecutionEloquentModel::where('workout_session_id', $sessionId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->count();
    }

    public function existsSetExecution(WorkoutSessionId $sessionId, ExerciseId $exerciseId, int $setNumber): bool
    {
        return SetExecutionEloquentModel::where('workout_session_id', $sessionId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->where('set_number', $setNumber)
            ->exists();
    }
}
