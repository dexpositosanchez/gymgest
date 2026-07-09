<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentDomainService;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use InvalidArgumentException;

class DeleteAssignmentUseCase
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

        $wasCurrentAssignment = $assignment->isCurrent();
        $studentId = $assignment->getStudentId();
        $gymId = $assignment->getGymId();

        // Delete assignment
        $this->assignmentRepository->delete($assignment);

        // If deleted was current, set most recent as current
        if ($wasCurrentAssignment) {
            $remainingAssignments = $this->assignmentRepository->findByStudentAndGym($studentId, $gymId);

            if (!empty($remainingAssignments)) {
                // Find most recent by startsAt
                $mostRecent = $remainingAssignments[0];
                foreach ($remainingAssignments as $remaining) {
                    if ($remaining->getStartsAt()->getValue() > $mostRecent->getStartsAt()->getValue()) {
                        $mostRecent = $remaining;
                    }
                }

                $this->domainService->setCurrentRoutine($studentId, $gymId, $mostRecent->getId());
            }
        }
    }
}
