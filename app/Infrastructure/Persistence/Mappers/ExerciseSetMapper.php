<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Routine\Entities\ExerciseSetEntity;
use App\Domain\Routine\ValueObjects\ExerciseSetId;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Routine\ValueObjects\SetNumber;
use App\Domain\Routine\ValueObjects\Reps;
use App\Infrastructure\Persistence\Eloquent\ExerciseSetEloquentModel;

class ExerciseSetMapper
{
    public static function toDomain(ExerciseSetEloquentModel $model): ExerciseSetEntity
    {
        return new ExerciseSetEntity(
            new ExerciseSetId($model->id),
            new RoutineDayExerciseId($model->routine_day_exercise_id),
            new SetNumber($model->set_number),
            new Reps($model->reps),
            $model->notes
        );
    }

    public static function toEloquent(ExerciseSetEntity $entity): ExerciseSetEloquentModel
    {
        $model = new ExerciseSetEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->routine_day_exercise_id = $entity->getRoutineDayExerciseId()->getValue();
        $model->set_number = $entity->getSetNumber()->getValue();
        $model->reps = $entity->getReps()->getValue();
        $model->notes = $entity->getNotes();

        return $model;
    }
}
