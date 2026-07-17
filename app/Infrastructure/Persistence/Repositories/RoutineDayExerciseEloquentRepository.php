<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Routine\Entities\RoutineDayExerciseEntity;
use App\Domain\Routine\Repositories\RoutineDayExerciseRepositoryInterface;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Infrastructure\Persistence\Eloquent\WorkoutSessionEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineAssignmentEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineDayExerciseEloquentModel;
use App\Infrastructure\Persistence\Mappers\RoutineDayExerciseMapper;

class RoutineDayExerciseEloquentRepository implements RoutineDayExerciseRepositoryInterface
{
    public function findBySessionAndExercise(
        WorkoutSessionId $sessionId,
        ExerciseId $exerciseId
    ): ?RoutineDayExerciseEntity {
        // Get session
        $session = WorkoutSessionEloquentModel::find($sessionId->getValue());
        if ($session === null) {
            return null;
        }

        // Get assignment
        $assignment = RoutineAssignmentEloquentModel::find($session->routine_assignment_id);
        if ($assignment === null) {
            return null;
        }

        // Find routine day exercise
        $model = RoutineDayExerciseEloquentModel::whereHas('routineDay', function ($query) use ($assignment, $session) {
            $query->where('routine_id', $assignment->routine_id)
                  ->where('day_number', $session->day_number);
        })
        ->where('exercise_id', $exerciseId->getValue())
        ->first();

        if ($model === null) {
            return null;
        }

        return RoutineDayExerciseMapper::toDomain($model);
    }
}
