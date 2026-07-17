<?php

declare(strict_types=1);

namespace App\Domain\ExerciseWeightHistory\Services;

use App\Domain\ExerciseWeightHistory\Repositories\ExerciseWeightHistoryRepositoryInterface;
use App\Domain\ExerciseWeightHistory\Entities\ExerciseWeightHistoryEntity;
use App\Domain\ExerciseWeightHistory\ValueObjects\Weight;
use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;

class WeightHistoryDomainService
{
    /** @var ExerciseWeightHistoryRepositoryInterface */
    private $historyRepository;

    public function __construct(ExerciseWeightHistoryRepositoryInterface $historyRepository)
    {
        $this->historyRepository = $historyRepository;
    }

    public function getSuggestedWeight(
        UserId $studentId,
        ExerciseId $exerciseId,
        Reps $reps
    ): ?Weight {
        $existing = $this->historyRepository->findByStudentExerciseAndReps($studentId, $exerciseId, $reps);

        if ($existing === null) {
            return null;
        }

        return $existing->getWeight();
    }

    public function shouldUpdateHistory(
        UserId $studentId,
        ExerciseId $exerciseId,
        Reps $reps,
        Weight $newWeight
    ): bool {
        $existing = $this->historyRepository->findByStudentExerciseAndReps($studentId, $exerciseId, $reps);

        if ($existing === null) {
            return true;
        }

        return $existing->shouldUpdate($newWeight);
    }
}
