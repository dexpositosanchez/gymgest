<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Exercise\Entities\MuscleGroupEntity;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Domain\Exercise\ValueObjects\MuscleGroupName;
use App\Infrastructure\Persistence\Eloquent\MuscleGroupEloquentModel;

class MuscleGroupMapper
{
    public static function toDomain(MuscleGroupEloquentModel $model): MuscleGroupEntity
    {
        return new MuscleGroupEntity(
            new MuscleGroupId($model->id),
            new MuscleGroupName($model->name),
            $model->description
        );
    }

    public static function toEloquent(MuscleGroupEntity $entity): MuscleGroupEloquentModel
    {
        $model = new MuscleGroupEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->name = $entity->getName()->getValue();
        $model->description = $entity->getDescription();

        return $model;
    }
}
