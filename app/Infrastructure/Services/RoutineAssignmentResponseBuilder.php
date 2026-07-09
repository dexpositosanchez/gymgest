<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Application\RoutineAssignment\Services\RoutineAssignmentResponseBuilderInterface;
use App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity;
use App\Application\RoutineAssignment\DTOs\RoutineAssignmentResponseDTO;
use App\Infrastructure\Persistence\Eloquent\RoutineEloquentModel;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;

class RoutineAssignmentResponseBuilder implements RoutineAssignmentResponseBuilderInterface
{
    public function buildFromEntity(RoutineAssignmentEntity $assignment): RoutineAssignmentResponseDTO
    {
        // Fetch related data (Infrastructure layer can use Eloquent)
        $routineModel = RoutineEloquentModel::find($assignment->getRoutineId()->getValue());
        $studentModel = UserEloquentModel::find($assignment->getStudentId()->getValue());
        $gymModel = GymEloquentModel::find($assignment->getGymId()->getValue());

        return new RoutineAssignmentResponseDTO(
            $assignment->getId()->getValue(),
            $assignment->getRoutineId()->getValue(),
            $routineModel ? $routineModel->name : '',
            $assignment->getStudentId()->getValue(),
            $studentModel ? ($studentModel->first_name . ' ' . $studentModel->last_name) : '',
            $assignment->getGymId()->getValue(),
            $gymModel ? $gymModel->name : '',
            $assignment->getAssignedAt()->getValue(),
            $assignment->getStartsAt()->getValue(),
            $assignment->isCurrent(),
            $assignment->getNotes()
        );
    }
}
