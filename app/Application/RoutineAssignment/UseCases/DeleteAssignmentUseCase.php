<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\UseCases;

use App\Domain\RoutineAssignment\Services\RoutineAssignmentCacheServiceInterface;
use App\Domain\Gym\Repositories\GymRepositoryInterface;
use App\Domain\RoutineAssignment\Repositories\RoutineAssignmentRepositoryInterface;
use App\Domain\RoutineAssignment\Services\RoutineAssignmentDomainService;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

class DeleteAssignmentUseCase
{
    private RoutineAssignmentRepositoryInterface $assignmentRepository;
    private GymRepositoryInterface $gymRepository;
    private RoutineAssignmentDomainService $domainService;
    private RoutineAssignmentCacheServiceInterface $cacheService;

    public function __construct(
        RoutineAssignmentRepositoryInterface $assignmentRepository,
        GymRepositoryInterface $gymRepository,
        RoutineAssignmentDomainService $domainService,
        RoutineAssignmentCacheServiceInterface $cacheService
    ) {
        $this->assignmentRepository = $assignmentRepository;
        $this->gymRepository = $gymRepository;
        $this->domainService = $domainService;
        $this->cacheService = $cacheService;
    }

    public function execute(string $assignmentId, string $trainerId): void
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

        $wasCurrentAssignment = $assignment->isCurrent();
        $studentId = $assignment->getStudentId();
        $gymId = $assignment->getGymId();

        // Eliminar asignación
        $this->assignmentRepository->delete($assignment);

        // Si la eliminada era la actual, establecer la más reciente como actual
        if ($wasCurrentAssignment) {
            $remainingAssignments = $this->assignmentRepository->findByStudentAndGym($studentId, $gymId);

            if (!empty($remainingAssignments)) {
                // Encontrar la más reciente por startsAt
                $mostRecent = $remainingAssignments[0];
                foreach ($remainingAssignments as $remaining) {
                    if ($remaining->getStartsAt()->getValue() > $mostRecent->getStartsAt()->getValue()) {
                        $mostRecent = $remaining;
                    }
                }

                $this->domainService->setCurrentRoutine($studentId, $gymId, $mostRecent->getId());
            }
        }

        // Invalidar caché del estudiante
        $this->cacheService->invalidate($studentId->getValue());
    }
}
