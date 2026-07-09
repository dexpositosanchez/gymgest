<?php

declare(strict_types=1);

namespace App\Application\Routine\UseCases;

use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\Services\RoutineDomainService;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;

class DeleteRoutineUseCase
{
    /** @var RoutineRepositoryInterface */
    private $routineRepository;

    /** @var RoutineDomainService */
    private $routineDomainService;

    /** @var RoutineAssignmentRepositoryInterface */
    private $assignmentRepository;

    public function __construct(
        RoutineRepositoryInterface $routineRepository,
        RoutineDomainService $routineDomainService,
        RoutineAssignmentRepositoryInterface $assignmentRepository
    ) {
        $this->routineRepository = $routineRepository;
        $this->routineDomainService = $routineDomainService;
        $this->assignmentRepository = $assignmentRepository;
    }

    public function execute(RoutineId $routineId, UserId $trainerId): void
    {
        $routine = $this->routineRepository->findById($routineId);

        if (!$routine) {
            throw new \DomainException('Rutina no encontrada');
        }

        // Verify routine belongs to trainer
        if (!$routine->belongsToTrainer($trainerId)) {
            throw new \DomainException('No tienes permiso para eliminar esta rutina');
        }

        // Check if routine is assigned
        if ($this->routineDomainService->isAssigned($routine->getId(), $this->assignmentRepository)) {
            throw new \DomainException('No se puede eliminar esta rutina porque está asignada a un alumno');
        }

        $this->routineRepository->delete($routineId);
    }
}
