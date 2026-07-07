<?php

declare(strict_types=1);

namespace App\Application\Routine\UseCases;

use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\User\ValueObjects\UserId;

class DeleteRoutineUseCase
{
    /** @var RoutineRepositoryInterface */
    private $routineRepository;

    public function __construct(RoutineRepositoryInterface $routineRepository)
    {
        $this->routineRepository = $routineRepository;
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
        if ($routine->isAssigned()) {
            throw new \DomainException('No se puede eliminar una rutina asignada a estudiantes');
        }

        $this->routineRepository->delete($routineId);
    }
}
