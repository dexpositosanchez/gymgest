<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;
use App\Infrastructure\Persistence\Mappers\GymMapper;

class GymEloquentRepository implements GymRepositoryInterface
{
    public function save(GymEntity $gym): void
    {
        $model = GymEloquentModel::find($gym->getId()->getValue());

        if ($model) {
            GymMapper::updateEloquentFromDomain($model, $gym);
            $model->save();
        } else {
            $model = GymMapper::toEloquent($gym);
            $model->save();
        }
    }

    public function findById(GymId $id): ?GymEntity
    {
        $model = GymEloquentModel::find($id->getValue());

        if (!$model) {
            return null;
        }

        return GymMapper::toDomain($model);
    }

    public function findByTrainerId(UserId $trainerId, bool $includeInactive = false): array
    {
        $query = GymEloquentModel::where('trainer_id', $trainerId->getValue());

        if (!$includeInactive) {
            $query->where('is_active', true);
        }

        $models = $query->get();

        $gyms = [];
        foreach ($models as $model) {
            $gyms[] = GymMapper::toDomain($model);
        }

        return $gyms;
    }

    public function delete(GymId $id): void
    {
        GymEloquentModel::where('id', $id->getValue())->delete();
    }
}
