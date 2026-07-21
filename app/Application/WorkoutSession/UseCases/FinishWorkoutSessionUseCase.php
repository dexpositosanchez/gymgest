<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\UseCases;

use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\User\ValueObjects\UserId;

class FinishWorkoutSessionUseCase
{
    /** @var WorkoutSessionRepositoryInterface */
    private $sessionRepository;

    public function __construct(WorkoutSessionRepositoryInterface $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    public function execute(string $sessionId, string $studentId, ?string $notes): void
    {
        $session = $this->sessionRepository->findById(new WorkoutSessionId($sessionId));

        // Verificar: la sesión debe existir
        if ($session === null) {
            throw new \DomainException('Sesión no encontrada');
        }

        // Verificar: la sesión debe pertenecer al estudiante
        if (!$session->getStudentId()->equals(new UserId($studentId))) {
            throw new \DomainException('Esta sesión no te pertenece');
        }

        // Verificar: la sesión debe estar activa
        if ($session->isFinished()) {
            throw new \DomainException('La sesión ya está finalizada');
        }

        $session->finish($notes);
        $this->sessionRepository->save($session);
    }
}
