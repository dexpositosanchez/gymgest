<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\WorkoutSession\Entities\WorkoutSessionEntity;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\WorkoutSessionEloquentModel;
use App\Infrastructure\Persistence\Mappers\WorkoutSessionMapper;

class WorkoutSessionEloquentRepository implements WorkoutSessionRepositoryInterface
{
    public function save(WorkoutSessionEntity $session): void
    {
        $model = WorkoutSessionEloquentModel::find($session->getId()->getValue());

        if ($model === null) {
            $model = WorkoutSessionMapper::toEloquent($session);
            $model->save();
        } else {
            WorkoutSessionMapper::updateEloquentFromDomain($model, $session);
            $model->save();
        }
    }

    public function findById(WorkoutSessionId $id): ?WorkoutSessionEntity
    {
        $model = WorkoutSessionEloquentModel::find($id->getValue());

        if ($model === null) {
            return null;
        }

        return WorkoutSessionMapper::toDomain($model);
    }

    public function findActiveByStudent(UserId $studentId): ?WorkoutSessionEntity
    {
        $model = WorkoutSessionEloquentModel::where('student_id', $studentId->getValue())
            ->where('is_active', true)
            ->first();

        if ($model === null) {
            return null;
        }

        return WorkoutSessionMapper::toDomain($model);
    }

    public function findHistoryByStudentId(UserId $studentId, int $page = 1, int $perPage = 15): array
    {
        $query = WorkoutSessionEloquentModel::where('student_id', $studentId->getValue())
            ->where('is_active', false)
            ->orderBy('started_at', 'desc');

        $total = $query->count();
        $models = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $entities = $models->map(fn($model) => WorkoutSessionMapper::toDomain($model))->all();

        return [
            'data' => $entities,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    public function hasActiveSession(UserId $studentId): bool
    {
        return WorkoutSessionEloquentModel::where('student_id', $studentId->getValue())
            ->where('is_active', true)
            ->exists();
    }
}
