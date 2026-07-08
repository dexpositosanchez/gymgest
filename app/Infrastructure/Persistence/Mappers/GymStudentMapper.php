<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Entities\GymStudentEntity;
use App\Domain\GymStudent\ValueObjects\GymStudentId;
use App\Domain\GymStudent\ValueObjects\QuotaExpiresAt;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;

class GymStudentMapper
{
    public function toDomain(GymStudentEloquentModel $model): GymStudentEntity
    {
        return new GymStudentEntity(
            new GymStudentId($model->id),
            new GymId($model->gym_id),
            new UserId($model->student_id),
            new QuotaExpiresAt($model->quota_expires_at->format('Y-m-d')),
            $model->is_active
        );
    }

    public function toEloquent(GymStudentEntity $entity): GymStudentEloquentModel
    {
        $model = GymStudentEloquentModel::findOrNew($entity->getId()->getValue());

        $model->id = $entity->getId()->getValue();
        $model->gym_id = $entity->getGymId()->getValue();
        $model->student_id = $entity->getStudentId()->getValue();
        $model->quota_expires_at = $entity->getQuotaExpiresAt()->getValue();
        $model->is_active = $entity->isActive();

        return $model;
    }
}
