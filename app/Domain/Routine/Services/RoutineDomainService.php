<?php

declare(strict_types=1);

namespace App\Domain\Routine\Services;

use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;

final class RoutineDomainService
{
    public function isAssigned(RoutineId $routineId, RoutineAssignmentRepositoryInterface $assignmentRepository): bool
    {
        return $assignmentRepository->countByRoutineId($routineId) > 0;
    }
}
