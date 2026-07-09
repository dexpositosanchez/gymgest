<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentDomainService;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use InvalidArgumentException;

class SetCurrentRoutineUseCase
{
    private RoutineAssignmentRepositoryInterface $assignmentRepository;
    private GymRepositoryInterface $gymRepository;
    private RoutineAssignmentDomainService $domainService;

    public function __construct(
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        GymRepositoryInterface $gymRepository,
        RoutineAssignmentDomainService $domainService
    ) {
        $this->assignmentRepository = $assignmentRepository;
        $this->gymRepository = $gymRepository;
        $this->domainService = $domainService;
    }

    public function execute(string $assignmentId, string $trainerId): void
    {
        // Guard: Find assignment
        $assignment = $this->assignmentRepository->findById(new RoutineAssignmentId($assignmentId));
        if (!$assignment) {
            throw new InvalidArgumentException('Assignment not found');
        }

        // Guard: Verify trainer owns gym
        $gym = $this->gymRepository->findById($assignment->getGymId());
        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }
        if ($gym->getTrainerId()->getValue() !== $trainerId) {
            throw new InvalidArgumentException('Unauthorized');
        }

        // Call domain service to set as current
        $this->domainService->setCurrentRoutine(
            $assignment->getStudentId(),
            $assignment->getGymId(),
            $assignment->getId()
        );
    }
}
