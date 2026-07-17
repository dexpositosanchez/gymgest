<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\WorkoutSession\Entities\WorkoutSessionEntity;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Routine\ValueObjects\DayNumber;
use App\Infrastructure\Persistence\Eloquent\WorkoutSessionEloquentModel;

class WorkoutSessionMapper
{
    public static function toDomain(WorkoutSessionEloquentModel $model): WorkoutSessionEntity
    {
        return new WorkoutSessionEntity(
            new WorkoutSessionId($model->id),
            new RoutineAssignmentId($model->routine_assignment_id),
            new UserId($model->student_id),
            new DayNumber($model->day_number),
            new \DateTimeImmutable($model->started_at->toDateTimeString()),
            $model->finished_at ? new \DateTimeImmutable($model->finished_at->toDateTimeString()) : null,
            $model->is_active,
            $model->notes
        );
    }

    public static function toEloquent(WorkoutSessionEntity $entity): WorkoutSessionEloquentModel
    {
        return new WorkoutSessionEloquentModel([
            'id' => $entity->getId()->getValue(),
            'routine_assignment_id' => $entity->getRoutineAssignmentId()->getValue(),
            'student_id' => $entity->getStudentId()->getValue(),
            'day_number' => $entity->getDayNumber()->getValue(),
            'started_at' => $entity->getStartedAt()->format('Y-m-d H:i:s'),
            'finished_at' => $entity->getFinishedAt() ? $entity->getFinishedAt()->format('Y-m-d H:i:s') : null,
            'is_active' => $entity->isActive(),
            'notes' => $entity->getNotes(),
        ]);
    }

    public static function updateEloquentFromDomain(
        WorkoutSessionEloquentModel $model,
        WorkoutSessionEntity $entity
    ): void {
        $model->routine_assignment_id = $entity->getRoutineAssignmentId()->getValue();
        $model->student_id = $entity->getStudentId()->getValue();
        $model->day_number = $entity->getDayNumber()->getValue();
        $model->started_at = $entity->getStartedAt()->format('Y-m-d H:i:s');
        $model->finished_at = $entity->getFinishedAt() ? $entity->getFinishedAt()->format('Y-m-d H:i:s') : null;
        $model->is_active = $entity->isActive();
        $model->notes = $entity->getNotes();
    }
}
