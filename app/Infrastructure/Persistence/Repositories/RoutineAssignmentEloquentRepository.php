<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\RoutineAssignmentEloquentModel;
use App\Infrastructure\Persistence\Mappers\RoutineAssignmentMapper;

final class RoutineAssignmentEloquentRepository implements RoutineAssignmentRepositoryInterface
{
    private RoutineAssignmentMapper $mapper;

    public function __construct(RoutineAssignmentMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function save(RoutineAssignmentEntity $assignment): void
    {
        $model = RoutineAssignmentEloquentModel::find($assignment->getId()->getValue());

        if ($model) {
            // Update existing
            $model->routine_id = $assignment->getRoutineId()->getValue();
            $model->student_id = $assignment->getStudentId()->getValue();
            $model->gym_id = $assignment->getGymId()->getValue();
            $model->assigned_at = $assignment->getAssignedAt()->getValue();
            $model->starts_at = $assignment->getStartsAt()->getValue();
            $model->is_current = $assignment->isCurrent();
            $model->notes = $assignment->getNotes();
            $model->save();
        } else {
            // Create new
            $model = $this->mapper->toEloquent($assignment);
            $model->save();
        }
    }

    public function findById(RoutineAssignmentId $id): ?RoutineAssignmentEntity
    {
        $model = RoutineAssignmentEloquentModel::find($id->getValue());

        if (!$model) {
            return null;
        }

        return $this->mapper->toDomain($model);
    }

    public function findByStudentAndGym(UserId $studentId, GymId $gymId): array
    {
        $models = RoutineAssignmentEloquentModel::where('student_id', $studentId->getValue())
            ->where('gym_id', $gymId->getValue())
            ->orderBy('is_current', 'desc')
            ->orderBy('starts_at', 'desc')
            ->get();

        $assignments = [];
        foreach ($models as $model) {
            $assignments[] = $this->mapper->toDomain($model);
        }

        return $assignments;
    }

    public function delete(RoutineAssignmentEntity $assignment): void
    {
        RoutineAssignmentEloquentModel::where('id', $assignment->getId()->getValue())->delete();
    }

    public function countByRoutineId(RoutineId $routineId): int
    {
        return RoutineAssignmentEloquentModel::where('routine_id', $routineId->getValue())->count();
    }

    public function findPendingByStartsAt(string $date): array
    {
        $models = RoutineAssignmentEloquentModel::where('starts_at', '<=', $date)
            ->where('is_current', false)
            ->get();

        $assignments = [];
        foreach ($models as $model) {
            $assignments[] = $this->mapper->toDomain($model);
        }

        return $assignments;
    }

    public function hasFutureCurrentAssignment(UserId $studentId, GymId $gymId, string $afterDate): bool
    {
        return RoutineAssignmentEloquentModel::where('student_id', $studentId->getValue())
            ->where('gym_id', $gymId->getValue())
            ->where('is_current', true)
            ->where('starts_at', '>', $afterDate)
            ->exists();
    }
}
