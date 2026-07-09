<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\Services;

use App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity;
use App\Application\RoutineAssignment\DTOs\RoutineAssignmentResponseDTO;

interface RoutineAssignmentResponseBuilderInterface
{
    public function buildFromEntity(RoutineAssignmentEntity $assignment): RoutineAssignmentResponseDTO;
}
