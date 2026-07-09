<?php

declare(strict_types=1);

namespace App\Domain\RoutineAssignment\Services;

use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Gym\ValueObjects\GymId;

final class RoutineAssignmentDomainService
{
    private $repository;

    public function __construct(RoutineAssignmentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function setCurrentRoutine(UserId $studentId, GymId $gymId, RoutineAssignmentId $assignmentId): void
    {
        // Fetch all assignments for this student+gym
        $assignments = $this->repository->findByStudentAndGym($studentId, $gymId);

        // Set isCurrent=false on all
        foreach ($assignments as $assignment) {
            $assignment->unsetAsCurrent();
            $this->repository->save($assignment);
        }

        // Set isCurrent=true on the specified one
        $targetAssignment = $this->repository->findById($assignmentId);
        if ($targetAssignment !== null) {
            $targetAssignment->setAsCurrent();
            $this->repository->save($targetAssignment);
        }
    }
}
