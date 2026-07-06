<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Exercise\Entities\TrainerExercisePreferenceEntity;
use App\Domain\Exercise\ValueObjects\PreferenceId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\TrainerExercisePreferenceEloquentModel;

class TrainerExercisePreferenceMapper
{
    public static function toDomain(TrainerExercisePreferenceEloquentModel $model): TrainerExercisePreferenceEntity
    {
        return new TrainerExercisePreferenceEntity(
            new PreferenceId($model->id),
            new UserId($model->trainer_id),
            new ExerciseId($model->exercise_id),
            $model->is_active
        );
    }

    public static function toEloquent(TrainerExercisePreferenceEntity $entity): TrainerExercisePreferenceEloquentModel
    {
        $model = new TrainerExercisePreferenceEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->trainer_id = $entity->getTrainerId()->getValue();
        $model->exercise_id = $entity->getExerciseId()->getValue();
        $model->is_active = $entity->isActive();

        return $model;
    }

    public static function updateEloquentFromDomain(TrainerExercisePreferenceEloquentModel $model, TrainerExercisePreferenceEntity $entity): void
    {
        $model->trainer_id = $entity->getTrainerId()->getValue();
        $model->exercise_id = $entity->getExerciseId()->getValue();
        $model->is_active = $entity->isActive();
    }
}
