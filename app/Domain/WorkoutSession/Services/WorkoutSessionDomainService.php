<?php

declare(strict_types=1);

namespace App\Domain\WorkoutSession\Services;

use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\SetExecution\Repositories\SetExecutionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;

class WorkoutSessionDomainService
{
    /** @var WorkoutSessionRepositoryInterface */
    private $workoutSessionRepository;

    /** @var SetExecutionRepositoryInterface */
    private $setExecutionRepository;

    public function __construct(
        WorkoutSessionRepositoryInterface $workoutSessionRepository,
        SetExecutionRepositoryInterface $setExecutionRepository
    ) {
        $this->workoutSessionRepository = $workoutSessionRepository;
        $this->setExecutionRepository = $setExecutionRepository;
    }

    public function canStartNewSession(UserId $studentId): bool
    {
        $activeSession = $this->workoutSessionRepository->findActiveByStudent($studentId);
        return $activeSession === null;
    }

    public function isExerciseCompleted(WorkoutSessionId $sessionId, ExerciseId $exerciseId, int $totalSets): bool
    {
        $completedSets = $this->setExecutionRepository->countCompletedSets($sessionId, $exerciseId);
        return $completedSets >= $totalSets;
    }
}
