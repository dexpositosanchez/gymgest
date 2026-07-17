<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\ExerciseWeightHistory\Entities\ExerciseWeightHistoryEntity;
use App\Domain\ExerciseWeightHistory\ValueObjects\ExerciseWeightHistoryId;
use App\Domain\ExerciseWeightHistory\ValueObjects\Weight;
use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Infrastructure\Persistence\Eloquent\ExerciseWeightHistoryEloquentModel;

class ExerciseWeightHistoryMapper
{
    public static function toDomain(ExerciseWeightHistoryEloquentModel $model): ExerciseWeightHistoryEntity
    {
        return new ExerciseWeightHistoryEntity(
            new ExerciseWeightHistoryId($model->id),
            new UserId($model->student_id),
            new ExerciseId($model->exercise_id),
            new Reps($model->reps),
            new Weight($model->weight),
            new \DateTimeImmutable($model->last_used_at->toDateTimeString())
        );
    }

    public static function toEloquent(ExerciseWeightHistoryEntity $entity): ExerciseWeightHistoryEloquentModel
    {
        return new ExerciseWeightHistoryEloquentModel([
            'id' => $entity->getId()->getValue(),
            'student_id' => $entity->getStudentId()->getValue(),
            'exercise_id' => $entity->getExerciseId()->getValue(),
            'reps' => $entity->getReps()->getValue(),
            'weight' => $entity->getWeight()->getValue(),
            'last_used_at' => $entity->getLastUsedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public static function updateEloquentFromDomain(
        ExerciseWeightHistoryEloquentModel $model,
        ExerciseWeightHistoryEntity $entity
    ): void {
        $model->weight = $entity->getWeight()->getValue();
        $model->last_used_at = $entity->getLastUsedAt()->format('Y-m-d H:i:s');
    }
}
