<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\Gym\ValueObjects\GymAddress;
use App\Domain\Gym\ValueObjects\GymLocality;
use App\Domain\Gym\ValueObjects\GymProvince;
use App\Domain\Gym\ValueObjects\GymCountry;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Gym\ValueObjects\GymName;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;

class GymMapper
{
    public static function toDomain(GymEloquentModel $model): GymEntity
    {
        return new GymEntity(
            new GymId($model->id),
            new UserId($model->trainer_id),
            new GymName($model->name),
            new GymAddress($model->address),
            new GymLocality($model->locality),
            new GymProvince($model->province),
            new GymCountry($model->country),
            $model->is_active,
            $model->is_personal_training ?? false
        );
    }

    public static function toEloquent(GymEntity $entity): GymEloquentModel
    {
        $model = new GymEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->trainer_id = $entity->getTrainerId()->getValue();
        $model->name = $entity->getName()->getValue();
        $model->address = $entity->getAddress()->getValue();
        $model->locality = $entity->getLocality()->getValue();
        $model->province = $entity->getProvince()->getValue();
        $model->country = $entity->getCountry()->getValue();
        $model->is_active = $entity->isActive();
        $model->is_personal_training = $entity->isPersonalTraining();

        return $model;
    }

    public static function updateEloquentFromDomain(GymEloquentModel $model, GymEntity $entity): void
    {
        $model->name = $entity->getName()->getValue();
        $model->address = $entity->getAddress()->getValue();
        $model->locality = $entity->getLocality()->getValue();
        $model->province = $entity->getProvince()->getValue();
        $model->country = $entity->getCountry()->getValue();
        $model->is_active = $entity->isActive();
        $model->is_personal_training = $entity->isPersonalTraining();
    }
}
