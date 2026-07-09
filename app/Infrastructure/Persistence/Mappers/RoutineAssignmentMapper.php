<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity;
use App\Domain\RoutineAssignment\ValueObjects\AssignedAt;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\RoutineAssignment\ValueObjects\StartsAt;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\RoutineAssignmentEloquentModel;
use DateTimeImmutable;

final class RoutineAssignmentMapper
{
    public function toDomain(RoutineAssignmentEloquentModel $model): RoutineAssignmentEntity
    {
        return new RoutineAssignmentEntity(
            RoutineAssignmentId::fromString($model->id),
            RoutineId::fromString($model->routine_id),
            UserId::fromString($model->student_id),
            GymId::fromString($model->gym_id),
            AssignedAt::fromString($model->assigned_at->format('Y-m-d H:i:s')),
            StartsAt::fromString($model->starts_at->format('Y-m-d')),
            $model->is_current,
            $model->notes
        );
    }

    public function toEloquent(RoutineAssignmentEntity $entity): RoutineAssignmentEloquentModel
    {
        $model = new RoutineAssignmentEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->routine_id = $entity->getRoutineId()->getValue();
        $model->student_id = $entity->getStudentId()->getValue();
        $model->gym_id = $entity->getGymId()->getValue();
        $model->assigned_at = $entity->getAssignedAt()->getValue();
        $model->starts_at = $entity->getStartsAt()->getValue();
        $model->is_current = $entity->isCurrent();
        $model->notes = $entity->getNotes();

        return $model;
    }
}
