<?php

declare(strict_types=1);

namespace App\Domain\SetExecution\Repositories;

use App\Domain\SetExecution\Entities\SetExecutionEntity;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;

interface SetExecutionRepositoryInterface
{
    public function save(SetExecutionEntity $setExecution): void;

    /**
     * @param WorkoutSessionId $sessionId
     * @return SetExecutionEntity[]
     */
    public function findBySessionId(WorkoutSessionId $sessionId): array;

    /**
     * @param WorkoutSessionId $sessionId
     * @param ExerciseId $exerciseId
     * @return SetExecutionEntity[]
     */
    public function findBySessionAndExercise(WorkoutSessionId $sessionId, ExerciseId $exerciseId): array;

    public function countCompletedSets(WorkoutSessionId $sessionId, ExerciseId $exerciseId): int;

    /**
     * Check if a specific set execution already exists
     *
     * @param WorkoutSessionId $sessionId
     * @param ExerciseId $exerciseId
     * @param int $setNumber
     * @return bool
     */
    public function existsSetExecution(WorkoutSessionId $sessionId, ExerciseId $exerciseId, int $setNumber): bool;
}
