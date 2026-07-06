<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Exercise\Entities\ExerciseEntity;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseName;
use App\Domain\Exercise\ValueObjects\ExerciseDescription;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Domain\Exercise\ValueObjects\ExerciseType;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\ExerciseEloquentModel;

class ExerciseMapper
{
    public static function toDomain(ExerciseEloquentModel $model): ExerciseEntity
    {
        $trainerId = $model->trainer_id !== null
            ? new UserId($model->trainer_id)
            : null;

        $type = $model->is_default
            ? ExerciseType::default()
            : ExerciseType::custom();

        return new ExerciseEntity(
            new ExerciseId($model->id),
            new ExerciseName($model->name),
            new ExerciseDescription($model->description),
            new MuscleGroupId($model->muscle_group_id),
            $type,
            $trainerId
        );
    }

    public static function toEloquent(ExerciseEntity $entity): ExerciseEloquentModel
    {
        $model = new ExerciseEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->name = $entity->getName()->getValue();
        $model->description = $entity->getDescription()->getValue();
        $model->muscle_group_id = $entity->getMuscleGroupId()->getValue();
        $model->trainer_id = $entity->getTrainerId() !== null
            ? $entity->getTrainerId()->getValue()
            : null;
        $model->is_default = $entity->getType()->isDefault();

        return $model;
    }

    public static function updateEloquentFromDomain(ExerciseEloquentModel $model, ExerciseEntity $entity): void
    {
        $model->name = $entity->getName()->getValue();
        $model->description = $entity->getDescription()->getValue();
        $model->muscle_group_id = $entity->getMuscleGroupId()->getValue();
        $model->trainer_id = $entity->getTrainerId() !== null
            ? $entity->getTrainerId()->getValue()
            : null;
        $model->is_default = $entity->getType()->isDefault();
    }
}
