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
        // Obtener todas las asignaciones para este estudiante+gym
        $assignments = $this->repository->findByStudentAndGym($studentId, $gymId);

        // Establecer isCurrent=false en todas
        foreach ($assignments as $assignment) {
            $assignment->unsetAsCurrent();
            $this->repository->save($assignment);
        }

        // Establecer isCurrent=true en la especificada
        $targetAssignment = $this->repository->findById($assignmentId);
        if ($targetAssignment !== null) {
            $targetAssignment->setAsCurrent();
            $this->repository->save($targetAssignment);
        }
    }
}
