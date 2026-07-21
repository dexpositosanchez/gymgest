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

        // Obtener todas las asignaciones donde startsAt <= hoy Y isCurrent=false
        $pendingAssignments = $this->assignmentRepository->findPendingByStartsAt($today);

        foreach ($pendingAssignments as $assignment) {
            // Verificar si ya existe una asignación actual para el mismo estudiante+gym con startsAt > hoy
            $futureCurrentExists = $this->assignmentRepository->hasFutureCurrentAssignment(
                $assignment->getStudentId(),
                $assignment->getGymId(),
                $today
            );

            if ($futureCurrentExists) {
                // Omitir esta asignación
                continue;
            }

            // Establecer esta asignación como actual
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
