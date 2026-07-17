<?php

declare(strict_types=1);

namespace App\Domain\Routine\Repositories;

use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;

interface ExerciseSetRepositoryInterface
{
    /**
     * @param RoutineDayExerciseId $routineDayExerciseId
     * @return \App\Domain\Routine\Entities\ExerciseSetEntity[]
     */
    public function findByRoutineDayExerciseId(RoutineDayExerciseId $routineDayExerciseId): array;
}
