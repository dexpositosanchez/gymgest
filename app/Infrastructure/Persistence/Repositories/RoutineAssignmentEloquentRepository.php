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

    public function findStudentRoutinesWithDetails(UserId $studentId, array $filters, int $page, int $perPage): array
    {
        $query = RoutineAssignmentEloquentModel::query()
            ->where('routine_assignments.student_id', $studentId->getValue())
            ->join('gym_students', function ($join) use ($studentId) {
                $join->on('routine_assignments.gym_id', '=', 'gym_students.gym_id')
                    ->where('gym_students.student_id', '=', $studentId->getValue())
                    ->where('gym_students.is_active', '=', true);
            })
            ->join('gyms', 'routine_assignments.gym_id', '=', 'gyms.id')
            ->join('routines', 'routine_assignments.routine_id', '=', 'routines.id')
            ->join('users', 'gyms.trainer_id', '=', 'users.id')
            ->select(
                'routine_assignments.*',
                'gyms.name as gym_name',
                'gyms.is_personal_training as gym_is_personal_training',
                'routines.name as routine_name',
                'routines.difficulty as routine_difficulty',
                'users.id as trainer_id',
                'users.name as trainer_name',
                'users.last_name as trainer_last_name',
                'users.email as trainer_email'
            );

        // Apply filters
        if (isset($filters['gym_id'])) {
            $query->where('routine_assignments.gym_id', $filters['gym_id']);
        }

        if (isset($filters['trainer_id'])) {
            $query->where('gyms.trainer_id', $filters['trainer_id']);
        }

        if (isset($filters['difficulty'])) {
            $query->where('routines.difficulty', $filters['difficulty']);
        }

        if (isset($filters['is_current'])) {
            $query->where('routine_assignments.is_current', $filters['is_current']);
        }

        if (isset($filters['from'])) {
            $query->where('routine_assignments.starts_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('routine_assignments.starts_at', '<=', $filters['to']);
        }

        // Order by is_current DESC, assigned_at DESC (uses optimized index)
        $query->orderBy('routine_assignments.is_current', 'desc')
            ->orderBy('routine_assignments.assigned_at', 'desc');

        // Paginate
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
