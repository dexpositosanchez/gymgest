<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Application\RoutineAssignment\DTOs\AssignRoutineDTO;
use App\Application\RoutineAssignment\DTOs\RoutineAssignmentResponseDTO;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentCacheServiceInterface;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentDomainService;
use App\Domain\RoutineAssignment\ValueObjects\AssignedAt;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\RoutineAssignment\ValueObjects\StartsAt;
use App\Domain\User\ValueObjects\UserId;
use App\Application\RoutineAssignment\Services\RoutineAssignmentResponseBuilderInterface;
use InvalidArgumentException;

class AssignRoutineUseCase
{
    private RoutineAssignmentRepositoryInterface $assignmentRepository;
    private RoutineRepositoryInterface $routineRepository;
    private GymStudentRepositoryInterface $gymStudentRepository;
    private GymRepositoryInterface $gymRepository;
    private RoutineAssignmentDomainService $domainService;
    private RoutineAssignmentResponseBuilderInterface $responseBuilder;
    private RoutineAssignmentCacheServiceInterface $cacheService;

    public function __construct(
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        RoutineRepositoryInterface $routineRepository,
        GymStudentRepositoryInterface $gymStudentRepository,
        GymRepositoryInterface $gymRepository,
        RoutineAssignmentDomainService $domainService,
        RoutineAssignmentResponseBuilderInterface $responseBuilder,
        RoutineAssignmentCacheServiceInterface $cacheService
    ) {
        $this->assignmentRepository = $assignmentRepository;
        $this->routineRepository = $routineRepository;
        $this->gymStudentRepository = $gymStudentRepository;
        $this->gymRepository = $gymRepository;
        $this->domainService = $domainService;
        $this->responseBuilder = $responseBuilder;
        $this->cacheService = $cacheService;
    }

    public function execute(AssignRoutineDTO $dto): RoutineAssignmentResponseDTO
    {
        // Verificar que el gimnasio existe
        $gym = $this->gymRepository->findById(new GymId($dto->gymId));
        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        // Verificar que la rutina existe
        $routine = $this->routineRepository->findById(new RoutineId($dto->routineId));
        if (!$routine) {
            throw new InvalidArgumentException('Routine not found');
        }

        // Verificar que la rutina pertenece al mismo entrenador que el gimnasio
        if (!$routine->belongsToTrainer($gym->getTrainerId())) {
            throw new InvalidArgumentException('Routine does not belong to gym trainer');
        }

        // Verificar que el estudiante existe y está activo en este gimnasio
        $gymStudent = $this->gymStudentRepository->findByGymAndStudent(
            new GymId($dto->gymId),
            new UserId($dto->studentId)
        );
        if (!$gymStudent) {
            throw new InvalidArgumentException('Student not enrolled in this gym');
        }
        if (!$gymStudent->isActive()) {
            throw new InvalidArgumentException('Student is not active in this gym');
        }

        // Crear asignación
        $assignment = new RoutineAssignmentEntity(
            RoutineAssignmentId::generate(),
            new RoutineId($dto->routineId),
            new UserId($dto->studentId),
            new GymId($dto->gymId),
            AssignedAt::now(),
            StartsAt::fromString($dto->startsAt),
            $dto->isCurrent,
            $dto->notes
        );

        // Guardar asignación (puede lanzar excepción si existe duplicado por restricción única)
        try {
            $this->assignmentRepository->save($assignment);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false ||
                strpos($e->getMessage(), 'unique constraint') !== false) {
                throw new InvalidArgumentException('Esta rutina ya está asignada a este alumno en este gimnasio');
            }
            throw $e;
        }

        // Si isCurrent=true, llamar al servicio de dominio para establecerla como actual
        if ($dto->isCurrent) {
            $this->domainService->setCurrentRoutine(
                new UserId($dto->studentId),
                new GymId($dto->gymId),
                $assignment->getId()
            );
        }

        // Invalidar caché del estudiante
        $this->cacheService->invalidate($dto->studentId);

        return $this->responseBuilder->buildFromEntity($assignment);
    }
}
