<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Routine\Repositories\ExerciseSetRepositoryInterface;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Infrastructure\Persistence\Eloquent\ExerciseSetEloquentModel;
use App\Infrastructure\Persistence\Mappers\ExerciseSetMapper;

class ExerciseSetEloquentRepository implements ExerciseSetRepositoryInterface
{
    public function findByRoutineDayExerciseId(RoutineDayExerciseId $routineDayExerciseId): array
    {
        $models = ExerciseSetEloquentModel::where('routine_day_exercise_id', $routineDayExerciseId->getValue())
            ->orderBy('set_number', 'asc')
            ->get();

        return $models->map(fn($model) => ExerciseSetMapper::toDomain($model))->all();
    }
}
