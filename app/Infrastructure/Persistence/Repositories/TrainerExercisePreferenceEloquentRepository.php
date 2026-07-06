<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Exercise\Entities\TrainerExercisePreferenceEntity;
use App\Domain\Exercise\Repositories\TrainerExercisePreferenceRepositoryInterface;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\TrainerExercisePreferenceEloquentModel;
use App\Infrastructure\Persistence\Mappers\TrainerExercisePreferenceMapper;

class TrainerExercisePreferenceEloquentRepository implements TrainerExercisePreferenceRepositoryInterface
{
    public function findByTrainerAndExercise(UserId $trainerId, ExerciseId $exerciseId): ?TrainerExercisePreferenceEntity
    {
        $model = TrainerExercisePreferenceEloquentModel::where('trainer_id', $trainerId->getValue())
            ->where('exercise_id', $exerciseId->getValue())
            ->first();

        return $model ? TrainerExercisePreferenceMapper::toDomain($model) : null;
    }

    public function save(TrainerExercisePreferenceEntity $preference): void
    {
        $model = TrainerExercisePreferenceEloquentModel::find($preference->getId()->getValue());

        if ($model) {
            TrainerExercisePreferenceMapper::updateEloquentFromDomain($model, $preference);
        } else {
            $model = TrainerExercisePreferenceMapper::toEloquent($preference);
        }

        $model->save();
    }
}
