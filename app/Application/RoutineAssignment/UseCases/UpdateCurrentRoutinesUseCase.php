<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentDomainService;
use DateTimeImmutable;

class UpdateCurrentRoutinesUseCase
{
    private RoutineAssignmentDomainService $domainService;
    private RoutineAssignmentRepositoryInterface $assignmentRepository;

    public function __construct(
        RoutineAssignmentDomainService $domainService,
        RoutineAssignmentRepositoryInterface $assignmentRepository
    ) {
        $this->domainService = $domainService;
        $this->assignmentRepository = $assignmentRepository;
    }

    public function execute(): int
    {
        $today = (new DateTimeImmutable())->format('Y-m-d');
        $updatedCount = 0;

        // Fetch all assignments where startsAt <= today AND isCurrent=false
        $pendingAssignments = $this->assignmentRepository->findPendingByStartsAt($today);

        foreach ($pendingAssignments as $assignment) {
            // Check if there's already a current assignment for same student+gym with startsAt > today
            $futureCurrentExists = $this->assignmentRepository->hasFutureCurrentAssignment(
                $assignment->getStudentId(),
                $assignment->getGymId(),
                $today
            );

            if ($futureCurrentExists) {
                // Skip this one
                continue;
            }

            // Set this assignment as current
            $this->domainService->setCurrentRoutine(
                $assignment->getStudentId(),
                $assignment->getGymId(),
                $assignment->getId()
            );

            $updatedCount++;
        }

        return $updatedCount;
    }
}
