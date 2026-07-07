<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Routine\Entities\RoutineEntity;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\RoutineEloquentModel;
use App\Infrastructure\Persistence\Mappers\RoutineMapper;

class RoutineEloquentRepository implements RoutineRepositoryInterface
{
    public function findById(RoutineId $id): ?RoutineEntity
    {
        $model = RoutineEloquentModel::with(['days.exercises.exercise.muscleGroup', 'days.exercises.sets'])->find($id->getValue());

        return $model ? RoutineMapper::toDomain($model) : null;
    }

    public function findByTrainer(UserId $trainerId, array $filters = []): array
    {
        $query = RoutineEloquentModel::with(['days.exercises.exercise.muscleGroup', 'days.exercises.sets'])
            ->where('trainer_id', $trainerId->getValue());

        // Filter by difficulty
        if (isset($filters['difficulty']) && $filters['difficulty'] !== null) {
            $query->where('difficulty', $filters['difficulty']);
        }

        // Filter by search term
        if (isset($filters['search']) && $filters['search'] !== null) {
            $query->where('name', 'LIKE', '%' . $filters['search'] . '%');
        }

        $models = $query->orderBy('created_at', 'desc')->get();

        return $models->map(function ($model) {
            return RoutineMapper::toDomain($model);
        })->all();
    }

    public function save(RoutineEntity $routine): void
    {
        $model = RoutineEloquentModel::find($routine->getId()->getValue());

        if ($model) {
            // Update existing routine
            RoutineMapper::updateEloquentFromDomain($model, $routine);
            $model->save();

            // Sync days and exercises
            RoutineMapper::syncDays($model, $routine);
        } else {
            // Create new routine
            $model = RoutineMapper::toEloquent($routine);
            $model->save();

            // Create days and exercises
            RoutineMapper::syncDays($model, $routine);
        }
    }

    public function delete(RoutineId $id): void
    {
        RoutineEloquentModel::destroy($id->getValue());
    }
}
