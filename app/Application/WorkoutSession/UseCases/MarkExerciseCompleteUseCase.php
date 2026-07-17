<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\UseCases;

use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\WorkoutSessionExerciseStatusEloquentModel;

class MarkExerciseCompleteUseCase
{
    /** @var WorkoutSessionRepositoryInterface */
    private $sessionRepository;

    public function __construct(WorkoutSessionRepositoryInterface $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    public function execute(string $sessionId, string $exerciseId, string $studentId): void
    {
        $sessionIdVO = new WorkoutSessionId($sessionId);

        // Guard: Session must exist
        $session = $this->sessionRepository->findById($sessionIdVO);
        if ($session === null) {
            throw new \DomainException('Sesión no encontrada');
        }

        // Guard: Session must belong to student
        if (!$session->getStudentId()->equals(new UserId($studentId))) {
            throw new \DomainException('Esta sesión no te pertenece');
        }

        // Guard: Session must be active
        if (!$session->isActive()) {
            throw new \DomainException('La sesión no está activa');
        }

        // Mark exercise as complete (using infrastructure directly - no domain concept)
        $existing = WorkoutSessionExerciseStatusEloquentModel::where('workout_session_id', $sessionId)
            ->where('exercise_id', $exerciseId)
            ->first();

        if ($existing) {
            $existing->is_completed = true;
            $existing->completed_at = now();
            $existing->save();
        } else {
            WorkoutSessionExerciseStatusEloquentModel::create([
                'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'workout_session_id' => $sessionId,
                'exercise_id' => $exerciseId,
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }
    }
}
