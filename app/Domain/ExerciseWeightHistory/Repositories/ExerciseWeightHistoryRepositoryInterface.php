<?php

declare(strict_types=1);

namespace App\Domain\ExerciseWeightHistory\Repositories;

use App\Domain\ExerciseWeightHistory\Entities\ExerciseWeightHistoryEntity;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;

interface ExerciseWeightHistoryRepositoryInterface
{
    public function upsert(ExerciseWeightHistoryEntity $history): void;

    public function findByStudentExerciseAndReps(
        UserId $studentId,
        ExerciseId $exerciseId,
        Reps $reps
    ): ?ExerciseWeightHistoryEntity;

    public function findSuggestedWeight(UserId $studentId, ExerciseId $exerciseId, Reps $reps): ?float;
}
