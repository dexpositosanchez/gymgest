<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\SetExecution\Entities\SetExecutionEntity;
use App\Domain\SetExecution\ValueObjects\SetExecutionId;
use App\Domain\SetExecution\ValueObjects\SetNumber;
use App\Domain\SetExecution\ValueObjects\RepsCompleted;
use App\Domain\SetExecution\ValueObjects\WeightUsed;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Infrastructure\Persistence\Eloquent\SetExecutionEloquentModel;

class SetExecutionMapper
{
    public static function toDomain(SetExecutionEloquentModel $model): SetExecutionEntity
    {
        return new SetExecutionEntity(
            new SetExecutionId($model->id),
            new WorkoutSessionId($model->workout_session_id),
            new RoutineDayExerciseId($model->routine_day_exercise_id),
            new ExerciseId($model->exercise_id),
            new SetNumber($model->set_number),
            new RepsCompleted($model->reps_completed),
            new WeightUsed($model->weight_used),
            new \DateTimeImmutable($model->completed_at->toDateTimeString())
        );
    }

    public static function toEloquent(SetExecutionEntity $entity): SetExecutionEloquentModel
    {
        return new SetExecutionEloquentModel([
            'id' => $entity->getId()->getValue(),
            'workout_session_id' => $entity->getWorkoutSessionId()->getValue(),
            'routine_day_exercise_id' => $entity->getRoutineDayExerciseId()->getValue(),
            'exercise_id' => $entity->getExerciseId()->getValue(),
            'set_number' => $entity->getSetNumber()->getValue(),
            'reps_completed' => $entity->getRepsCompleted()->getValue(),
            'weight_used' => $entity->getWeightUsed()->getValue(),
            'completed_at' => $entity->getCompletedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
