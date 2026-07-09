<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Application\RoutineAssignment\DTOs\AssignRoutineDTO;
use App\Application\RoutineAssignment\DTOs\RoutineAssignmentResponseDTO;
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

    public function __construct(
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        RoutineRepositoryInterface $routineRepository,
        GymStudentRepositoryInterface $gymStudentRepository,
        GymRepositoryInterface $gymRepository,
        RoutineAssignmentDomainService $domainService,
        RoutineAssignmentResponseBuilderInterface $responseBuilder
    ) {
        $this->assignmentRepository = $assignmentRepository;
        $this->routineRepository = $routineRepository;
        $this->gymStudentRepository = $gymStudentRepository;
        $this->gymRepository = $gymRepository;
        $this->domainService = $domainService;
        $this->responseBuilder = $responseBuilder;
    }

    public function execute(AssignRoutineDTO $dto): RoutineAssignmentResponseDTO
    {
        // Guard: Verify gym exists
        $gym = $this->gymRepository->findById(new GymId($dto->gymId));
        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        // Guard: Verify routine exists
        $routine = $this->routineRepository->findById(new RoutineId($dto->routineId));
        if (!$routine) {
            throw new InvalidArgumentException('Routine not found');
        }

        // Guard: Verify routine belongs to same trainer as gym
        if (!$routine->getTrainerId()->equals($gym->getTrainerId())) {
            throw new InvalidArgumentException('Routine does not belong to gym trainer');
        }

        // Guard: Verify student exists and is active in this gym
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

        // Create assignment
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

        // Save assignment (may throw exception if duplicate due to unique constraint)
        try {
            $this->assignmentRepository->save($assignment);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false ||
                strpos($e->getMessage(), 'unique constraint') !== false) {
                throw new InvalidArgumentException('Esta rutina ya está asignada a este alumno en este gimnasio');
            }
            throw $e;
        }

        // If isCurrent=true, call domain service to set it as current
        if ($dto->isCurrent) {
            $this->domainService->setCurrentRoutine(
                new UserId($dto->studentId),
                new GymId($dto->gymId),
                $assignment->getId()
            );
        }

        return $this->responseBuilder->buildFromEntity($assignment);
    }
}
