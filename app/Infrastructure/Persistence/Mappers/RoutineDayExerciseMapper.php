<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Routine\Entities\RoutineDayExerciseEntity;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Routine\ValueObjects\RoutineDayId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Routine\ValueObjects\OrderIndex;
use App\Infrastructure\Persistence\Eloquent\RoutineDayExerciseEloquentModel;

class RoutineDayExerciseMapper
{
    public static function toDomain(RoutineDayExerciseEloquentModel $model): RoutineDayExerciseEntity
    {
        // Map sets if loaded
        $sets = [];
        if ($model->relationLoaded('sets')) {
            foreach ($model->sets as $setModel) {
                $sets[] = ExerciseSetMapper::toDomain($setModel);
            }
        }

        return new RoutineDayExerciseEntity(
            new RoutineDayExerciseId($model->id),
            new RoutineDayId($model->routine_day_id),
            new ExerciseId($model->exercise_id),
            new OrderIndex($model->order_index),
            $sets,
            $model->notes
        );
    }

    public static function toEloquent(RoutineDayExerciseEntity $entity): RoutineDayExerciseEloquentModel
    {
        $model = new RoutineDayExerciseEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->routine_day_id = $entity->getRoutineDayId()->getValue();
        $model->exercise_id = $entity->getExerciseId()->getValue();
        $model->order_index = $entity->getOrderIndex()->getValue();
        $model->notes = $entity->getNotes();

        return $model;
    }
}
