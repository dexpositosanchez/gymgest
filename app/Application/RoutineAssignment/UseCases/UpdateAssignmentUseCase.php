<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Application\RoutineAssignment\DTOs\UpdateAssignmentDTO;
use App\Application\RoutineAssignment\DTOs\RoutineAssignmentResponseDTO;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentDomainService;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\RoutineAssignment\ValueObjects\StartsAt;
use App\Domain\User\ValueObjects\UserId;
use App\Application\RoutineAssignment\Services\RoutineAssignmentResponseBuilderInterface;
use InvalidArgumentException;

class UpdateAssignmentUseCase
{
    private RoutineAssignmentRepositoryInterface $assignmentRepository;
    private GymRepositoryInterface $gymRepository;
    private RoutineAssignmentDomainService $domainService;
    private RoutineAssignmentResponseBuilderInterface $responseBuilder;

    public function __construct(
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        GymRepositoryInterface $gymRepository,
        RoutineAssignmentDomainService $domainService,
        RoutineAssignmentResponseBuilderInterface $responseBuilder
    ) {
        $this->assignmentRepository = $assignmentRepository;
        $this->gymRepository = $gymRepository;
        $this->domainService = $domainService;
        $this->responseBuilder = $responseBuilder;
    }

    public function execute(string $assignmentId, UpdateAssignmentDTO $dto, string $trainerId): RoutineAssignmentResponseDTO
    {
        // Buscar asignación
        $assignment = $this->assignmentRepository->findById(new RoutineAssignmentId($assignmentId));
        if (!$assignment) {
            throw new InvalidArgumentException('Assignment not found');
        }

        // Verificar que el entrenador es dueño del gimnasio
        $gym = $this->gymRepository->findById($assignment->getGymId());
        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }
        if (!$gym->belongsToTrainer(new UserId($trainerId))) {
            throw new InvalidArgumentException('Unauthorized');
        }

        // Actualizar campos
        if ($dto->startsAt !== null) {
            $assignment->updateStartsAt(StartsAt::fromString($dto->startsAt));
        }
        if ($dto->notes !== null) {
            $assignment->updateNotes($dto->notes);
        }

        // Guardar cambios
        $this->assignmentRepository->save($assignment);

        // Si isCurrent=true, llamar al servicio de dominio
        if ($dto->isCurrent === true) {
            $this->domainService->setCurrentRoutine(
                $assignment->getStudentId(),
                $assignment->getGymId(),
                $assignment->getId()
            );
        }

        return $this->responseBuilder->buildFromEntity($assignment);
    }
}
