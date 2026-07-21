<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\UseCases;

use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionExerciseStatusRepositoryInterface;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;

class MarkExerciseCompleteUseCase
{
    /** @var WorkoutSessionRepositoryInterface */
    private $sessionRepository;

    /** @var WorkoutSessionExerciseStatusRepositoryInterface */
    private $exerciseStatusRepository;

    public function __construct(
        WorkoutSessionRepositoryInterface $sessionRepository,
        WorkoutSessionExerciseStatusRepositoryInterface $exerciseStatusRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->exerciseStatusRepository = $exerciseStatusRepository;
    }

    public function execute(string $sessionId, string $exerciseId, string $studentId): void
    {
        $sessionIdVO = new WorkoutSessionId($sessionId);
        $exerciseIdVO = new ExerciseId($exerciseId);

        // Verificar: la sesión debe existir
        $session = $this->sessionRepository->findById($sessionIdVO);
        if ($session === null) {
            throw new \DomainException('Sesión no encontrada');
        }

        // Verificar: la sesión debe pertenecer al estudiante
        if (!$session->getStudentId()->equals(new UserId($studentId))) {
            throw new \DomainException('Esta sesión no te pertenece');
        }

        // Verificar: la sesión debe estar activa
        if (!$session->isActive()) {
            throw new \DomainException('La sesión no está activa');
        }

        // Marcar ejercicio como completado
        $this->exerciseStatusRepository->markAsCompleted($sessionIdVO, $exerciseIdVO);
    }
}
