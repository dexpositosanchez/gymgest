<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Exercise\Entities\MuscleGroupEntity;
use App\Domain\Exercise\Repositories\MuscleGroupRepositoryInterface;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Infrastructure\Persistence\Eloquent\MuscleGroupEloquentModel;
use App\Infrastructure\Persistence\Mappers\MuscleGroupMapper;

class MuscleGroupEloquentRepository implements MuscleGroupRepositoryInterface
{
    public function findAll(): array
    {
        $models = MuscleGroupEloquentModel::all();

        return $models->map(function (MuscleGroupEloquentModel $model) {
            return MuscleGroupMapper::toDomain($model);
        })->all();
    }

    public function findById(MuscleGroupId $id): ?MuscleGroupEntity
    {
        $model = MuscleGroupEloquentModel::find($id->getValue());

        return $model ? MuscleGroupMapper::toDomain($model) : null;
    }
}
