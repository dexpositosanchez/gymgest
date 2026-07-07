<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Routine\Entities\RoutineEntity;
use App\Domain\Routine\Entities\RoutineDayEntity;
use App\Domain\Routine\Entities\RoutineDayExerciseEntity;
use App\Domain\Routine\Entities\ExerciseSetEntity;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\RoutineName;
use App\Domain\Routine\ValueObjects\RoutineDescription;
use App\Domain\Routine\ValueObjects\RoutineDifficulty;
use App\Domain\Routine\ValueObjects\RoutineDayId;
use App\Domain\Routine\ValueObjects\DayNumber;
use App\Domain\Routine\ValueObjects\DayName;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Routine\ValueObjects\OrderIndex;
use App\Domain\Routine\ValueObjects\ExerciseSetId;
use App\Domain\Routine\ValueObjects\SetNumber;
use App\Domain\Routine\ValueObjects\Reps;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\RoutineEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineDayEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineDayExerciseEloquentModel;
use App\Infrastructure\Persistence\Eloquent\ExerciseSetEloquentModel;

class RoutineMapper
{
    public static function toDomain(RoutineEloquentModel $model): RoutineEntity
    {
        $days = [];
        foreach ($model->days as $dayModel) {
            $exercises = [];
            foreach ($dayModel->exercises as $exerciseModel) {
                $sets = [];
                foreach ($exerciseModel->sets as $setModel) {
                    $sets[] = new ExerciseSetEntity(
                        new ExerciseSetId($setModel->id),
                        new RoutineDayExerciseId($setModel->routine_day_exercise_id),
                        new SetNumber($setModel->set_number),
                        new Reps($setModel->reps),
                        $setModel->notes
                    );
                }

                $exercises[] = new RoutineDayExerciseEntity(
                    new RoutineDayExerciseId($exerciseModel->id),
                    new RoutineDayId($exerciseModel->routine_day_id),
                    new ExerciseId($exerciseModel->exercise_id),
                    new OrderIndex($exerciseModel->order_index),
                    $sets,
                    $exerciseModel->notes
                );
            }

            $days[] = new RoutineDayEntity(
                new RoutineDayId($dayModel->id),
                new RoutineId($dayModel->routine_id),
                new DayNumber($dayModel->day_number),
                new DayName($dayModel->name),
                $exercises
            );
        }

        $routine = new RoutineEntity(
            new RoutineId($model->id),
            new UserId($model->trainer_id),
            new RoutineName($model->name),
            $model->description ? new RoutineDescription($model->description) : null,
            RoutineDifficulty::fromString($model->difficulty),
            $days
        );

        return $routine;
    }

    public static function toEloquent(RoutineEntity $entity): RoutineEloquentModel
    {
        $model = new RoutineEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->trainer_id = $entity->getTrainerId()->getValue();
        $model->name = $entity->getName()->getValue();
        $model->description = $entity->getDescription() ? $entity->getDescription()->getValue() : null;
        $model->difficulty = $entity->getDifficulty()->getValue();

        return $model;
    }

    public static function updateEloquentFromDomain(RoutineEloquentModel $model, RoutineEntity $entity): void
    {
        $model->name = $entity->getName()->getValue();
        $model->description = $entity->getDescription() ? $entity->getDescription()->getValue() : null;
        $model->difficulty = $entity->getDifficulty()->getValue();
    }

    public static function syncDays(RoutineEloquentModel $model, RoutineEntity $entity): void
    {
        // Delete existing days (CASCADE will delete exercises and sets)
        $model->days()->delete();

        // Create new days, exercises and sets
        foreach ($entity->getDays() as $dayEntity) {
            $dayModel = new RoutineDayEloquentModel();
            $dayModel->id = $dayEntity->getId()->getValue();
            $dayModel->routine_id = $entity->getId()->getValue();
            $dayModel->day_number = $dayEntity->getDayNumber()->getValue();
            $dayModel->name = $dayEntity->getName()->getValue();
            $dayModel->save();

            foreach ($dayEntity->getExercises() as $exerciseEntity) {
                $exerciseModel = new RoutineDayExerciseEloquentModel();
                $exerciseModel->id = $exerciseEntity->getId()->getValue();
                $exerciseModel->routine_day_id = $dayEntity->getId()->getValue();
                $exerciseModel->exercise_id = $exerciseEntity->getExerciseId()->getValue();
                $exerciseModel->order_index = $exerciseEntity->getOrderIndex()->getValue();
                $exerciseModel->notes = $exerciseEntity->getNotes();
                $exerciseModel->save();

                foreach ($exerciseEntity->getSets() as $setEntity) {
                    $setModel = new ExerciseSetEloquentModel();
                    $setModel->id = $setEntity->getId()->getValue();
                    $setModel->routine_day_exercise_id = $exerciseEntity->getId()->getValue();
                    $setModel->set_number = $setEntity->getSetNumber()->getValue();
                    $setModel->reps = $setEntity->getReps()->getValue();
                    $setModel->notes = $setEntity->getNotes();
                    $setModel->save();
                }
            }
        }
    }
}
