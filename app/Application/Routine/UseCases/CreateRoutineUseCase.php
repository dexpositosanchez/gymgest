<?php

declare(strict_types=1);

namespace App\Application\Routine\UseCases;

use App\Application\Routine\DTOs\CreateRoutineDTO;
use App\Domain\Routine\Entities\RoutineEntity;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\Services\RoutineReconstructionService;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\RoutineName;
use App\Domain\Routine\ValueObjects\RoutineDescription;
use App\Domain\Routine\ValueObjects\RoutineDifficulty;
use App\Domain\User\ValueObjects\UserId;

class CreateRoutineUseCase
{
    /** @var RoutineRepositoryInterface */
    private $routineRepository;

    /** @var RoutineReconstructionService */
    private $reconstructionService;

    public function __construct(
        RoutineRepositoryInterface $routineRepository,
        RoutineReconstructionService $reconstructionService
    ) {
        $this->routineRepository = $routineRepository;
        $this->reconstructionService = $reconstructionService;
    }

    public function execute(CreateRoutineDTO $dto, UserId $trainerId): RoutineEntity
    {
        // Crear entidad de rutina
        $routine = new RoutineEntity(
            RoutineId::generate(),
            $trainerId,
            new RoutineName($dto->name),
            $dto->description ? new RoutineDescription($dto->description) : null,
            RoutineDifficulty::fromString($dto->difficulty)
        );

        // Usar servicio de reconstrucción para construir estructura de días
        $days = $this->reconstructionService->reconstructDays($dto->days, $routine->getId());
        $routine->setDays($days);

        $this->routineRepository->save($routine);

        return $routine;
    }
}
