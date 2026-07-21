<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Exercise\Entities\ExerciseEntity;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;
use App\Application\Exercise\DTOs\ExerciseFilterDTO;
use App\Infrastructure\Persistence\Eloquent\ExerciseEloquentModel;
use App\Infrastructure\Persistence\Mappers\ExerciseMapper;

class ExerciseEloquentRepository implements ExerciseRepositoryInterface
{
    public function findById(ExerciseId $id): ?ExerciseEntity
    {
        $model = ExerciseEloquentModel::find($id->getValue());

        return $model ? ExerciseMapper::toDomain($model) : null;
    }

    public function findByTrainerWithPreferences(UserId $trainerId, ExerciseFilterDTO $filters): array
    {
        $query = ExerciseEloquentModel::query()
            ->leftJoin('trainer_exercise_preferences', function ($join) use ($trainerId) {
                $join->on('exercises.id', '=', 'trainer_exercise_preferences.exercise_id')
                    ->where('trainer_exercise_preferences.trainer_id', '=', $trainerId->getValue());
            })
            ->select('exercises.*', 'trainer_exercise_preferences.is_active as preference_is_active');

        // Filter by muscle_group_id if provided
        if ($filters->muscleGroupId !== null) {
            $query->where('exercises.muscle_group_id', $filters->muscleGroupId);
        }

        // Filter by search term (LIKE on name)
        if ($filters->search !== null) {
            $query->where('exercises.name', 'LIKE', '%' . $filters->search . '%');
        }

        // Filter by type (default or custom)
        if ($filters->type !== null) {
            if ($filters->type === 'default') {
                $query->where('exercises.is_default', true);
            } elseif ($filters->type === 'custom') {
                $query->where('exercises.is_default', false)
                    ->where('exercises.trainer_id', $trainerId->getValue());
            }
        }

        // Filter by include_inactive
        if (!$filters->includeInactive) {
            $query->where(function ($q) {
                $q->whereNull('trainer_exercise_preferences.is_active')
                    ->orWhere('trainer_exercise_preferences.is_active', true);
            });
        }

        $models = $query->get();

        // Map to domain entities and attach preference_is_active as metadata
        return $models->map(function ($model) {
            $exercise = ExerciseMapper::toDomain($model);
            // Store preference_is_active in a property for later use
            $exercise->preferenceIsActive = $model->preference_is_active;
            return $exercise;
        })->all();
    }

    public function save(ExerciseEntity $exercise): void
    {
        $model = ExerciseEloquentModel::find($exercise->getId()->getValue());

        if ($model) {
            ExerciseMapper::updateEloquentFromDomain($model, $exercise);
        } else {
            $model = ExerciseMapper::toEloquent($exercise);
        }

        $model->save();
    }

    public function delete(ExerciseId $id): void
    {
        ExerciseEloquentModel::destroy($id->getValue());
    }

    public function getMuscleGroupName(ExerciseId $exerciseId): ?string
    {
        $exerciseModel = ExerciseEloquentModel::with('muscleGroup')->find($exerciseId->getValue());

        if (!$exerciseModel || !$exerciseModel->muscleGroup) {
            return null;
        }

        return $exerciseModel->muscleGroup->name;
    }
}
