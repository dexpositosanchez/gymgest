<?php

declare(strict_types=1);

namespace App\Application\Routine\UseCases;

use App\Application\Routine\DTOs\UpdateRoutineDTO;
use App\Domain\Routine\Entities\RoutineEntity;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\Services\RoutineDomainService;
use App\Domain\Routine\Services\RoutineReconstructionService;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\RoutineName;
use App\Domain\Routine\ValueObjects\RoutineDescription;
use App\Domain\Routine\ValueObjects\RoutineDifficulty;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;

class UpdateRoutineUseCase
{
    /** @var RoutineRepositoryInterface */
    private $routineRepository;

    /** @var RoutineDomainService */
    private $routineDomainService;

    /** @var RoutineAssignmentRepositoryInterface */
    private $assignmentRepository;

    /** @var RoutineReconstructionService */
    private $reconstructionService;

    public function __construct(
        RoutineRepositoryInterface $routineRepository,
        RoutineDomainService $routineDomainService,
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        RoutineReconstructionService $reconstructionService
    ) {
        $this->routineRepository = $routineRepository;
        $this->routineDomainService = $routineDomainService;
        $this->assignmentRepository = $assignmentRepository;
        $this->reconstructionService = $reconstructionService;
    }

    public function execute(RoutineId $routineId, UpdateRoutineDTO $dto, UserId $trainerId): RoutineEntity
    {
        $routine = $this->routineRepository->findById($routineId);

        if (!$routine) {
            throw new \DomainException('Rutina no encontrada');
        }

        // Verificar que la rutina pertenece al entrenador
        if (!$routine->belongsToTrainer($trainerId)) {
            throw new \DomainException('No tienes permiso para editar esta rutina');
        }

        // Verificar si la rutina está asignada
        if ($this->routineDomainService->isAssigned($routine->getId(), $this->assignmentRepository)) {
            throw new \DomainException('Cannot update routine with active assignments');
        }

        // Actualizar detalles básicos
        $routine->updateDetails(
            new RoutineName($dto->name),
            $dto->description ? new RoutineDescription($dto->description) : null,
            RoutineDifficulty::fromString($dto->difficulty)
        );

        // Usar servicio de reconstrucción para reconstruir estructura de días
        $days = $this->reconstructionService->reconstructDays($dto->days, $routine->getId());
        $routine->setDays($days);

        $this->routineRepository->save($routine);

        return $routine;
    }
}
