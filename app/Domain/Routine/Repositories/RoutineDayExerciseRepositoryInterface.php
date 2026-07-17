<?php

declare(strict_types=1);

namespace App\Domain\Routine\Repositories;

use App\Domain\Routine\Entities\RoutineDayExerciseEntity;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;

interface RoutineDayExerciseRepositoryInterface
{
    public function findBySessionAndExercise(
        WorkoutSessionId $sessionId,
        ExerciseId $exerciseId
    ): ?RoutineDayExerciseEntity;
}
