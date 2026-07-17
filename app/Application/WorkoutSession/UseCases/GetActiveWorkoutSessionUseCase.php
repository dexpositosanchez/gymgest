<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\UseCases;

use App\Domain\WorkoutSession\Entities\WorkoutSessionEntity;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class GetActiveWorkoutSessionUseCase
{
    /** @var WorkoutSessionRepositoryInterface */
    private $sessionRepository;

    public function __construct(WorkoutSessionRepositoryInterface $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    public function execute(string $studentId): ?WorkoutSessionEntity
    {
        return $this->sessionRepository->findActiveByStudent(new UserId($studentId));
    }
}
