<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\UseCases;

use App\Domain\WorkoutSession\Entities\WorkoutSessionEntity;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\WorkoutSession\Services\WorkoutSessionDomainService;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\ValueObjects\DayNumber;
use App\Domain\User\ValueObjects\UserId;

class StartWorkoutSessionUseCase
{
    /** @var WorkoutSessionDomainService */
    private $domainService;

    /** @var WorkoutSessionRepositoryInterface */
    private $sessionRepository;

    /** @var RoutineAssignmentRepositoryInterface */
    private $assignmentRepository;

    /** @var RoutineRepositoryInterface */
    private $routineRepository;

    public function __construct(
        WorkoutSessionDomainService $domainService,
        WorkoutSessionRepositoryInterface $sessionRepository,
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        RoutineRepositoryInterface $routineRepository
    ) {
        $this->domainService = $domainService;
        $this->sessionRepository = $sessionRepository;
        $this->assignmentRepository = $assignmentRepository;
        $this->routineRepository = $routineRepository;
    }

    public function execute(
        string $studentId,
        string $routineAssignmentId,
        int $dayNumber,
        ?string $notes
    ): WorkoutSessionEntity {
        $studentIdVO = new UserId($studentId);

        // Guard: Cannot start if already has active session
        if (!$this->domainService->canStartNewSession($studentIdVO)) {
            throw new \DomainException('Ya tienes una sesión activa');
        }

        // Guard: Routine assignment must exist
        $assignment = $this->assignmentRepository->findById(new RoutineAssignmentId($routineAssignmentId));
        if ($assignment === null) {
            throw new \DomainException('Rutina no asignada');
        }

        // Guard: Assignment must belong to student
        if (!$assignment->getStudentId()->equals($studentIdVO)) {
            throw new \DomainException('Esta rutina no está asignada a ti');
        }

        // Guard: Day number must exist in routine
        $routine = $this->routineRepository->findById($assignment->getRoutineId());
        if ($routine === null) {
            throw new \DomainException('Rutina no encontrada');
        }

        if (!$routine->hasDayNumber($dayNumber)) {
            throw new \DomainException("El día {$dayNumber} no existe en esta rutina");
        }

        // Create workout session
        $session = new WorkoutSessionEntity(
            WorkoutSessionId::generate(),
            new RoutineAssignmentId($routineAssignmentId),
            $studentIdVO,
            new DayNumber($dayNumber),
            new \DateTimeImmutable(),
            null,
            true,
            $notes
        );

        $this->sessionRepository->save($session);

        return $session;
    }
}
