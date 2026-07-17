<?php

declare(strict_types=1);

namespace App\Application\SetExecution\UseCases;

use App\Domain\SetExecution\Entities\SetExecutionEntity;
use App\Domain\SetExecution\ValueObjects\SetExecutionId;
use App\Domain\SetExecution\ValueObjects\SetNumber;
use App\Domain\SetExecution\ValueObjects\RepsCompleted;
use App\Domain\SetExecution\ValueObjects\WeightUsed;
use App\Domain\SetExecution\Repositories\SetExecutionRepositoryInterface;
use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\ExerciseWeightHistory\Entities\ExerciseWeightHistoryEntity;
use App\Domain\ExerciseWeightHistory\ValueObjects\ExerciseWeightHistoryId;
use App\Domain\ExerciseWeightHistory\ValueObjects\Weight;
use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;
use App\Domain\ExerciseWeightHistory\Repositories\ExerciseWeightHistoryRepositoryInterface;
use App\Domain\ExerciseWeightHistory\Services\WeightHistoryDomainService;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Routine\Repositories\RoutineDayExerciseRepositoryInterface;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\User\ValueObjects\UserId;

class ExecuteSetUseCase
{
    /** @var SetExecutionRepositoryInterface */
    private $setExecutionRepository;

    /** @var WorkoutSessionRepositoryInterface */
    private $sessionRepository;

    /** @var RoutineDayExerciseRepositoryInterface */
    private $routineDayExerciseRepository;

    /** @var ExerciseWeightHistoryRepositoryInterface */
    private $historyRepository;

    /** @var WeightHistoryDomainService */
    private $historyDomainService;

    public function __construct(
        SetExecutionRepositoryInterface $setExecutionRepository,
        WorkoutSessionRepositoryInterface $sessionRepository,
        RoutineDayExerciseRepositoryInterface $routineDayExerciseRepository,
        ExerciseWeightHistoryRepositoryInterface $historyRepository,
        WeightHistoryDomainService $historyDomainService
    ) {
        $this->setExecutionRepository = $setExecutionRepository;
        $this->sessionRepository = $sessionRepository;
        $this->routineDayExerciseRepository = $routineDayExerciseRepository;
        $this->historyRepository = $historyRepository;
        $this->historyDomainService = $historyDomainService;
    }

    public function execute(
        string $sessionId,
        string $exerciseId,
        int $setNumber,
        int $repsCompleted,
        ?float $weightUsed
    ): SetExecutionEntity {
        $sessionIdVO = new WorkoutSessionId($sessionId);
        $exerciseIdVO = new ExerciseId($exerciseId);

        // Guard: Session must exist
        $session = $this->sessionRepository->findById($sessionIdVO);
        if ($session === null) {
            throw new \DomainException('Sesión no encontrada');
        }

        // Guard: Session must allow adding sets
        if (!$session->canAddSets()) {
            throw new \DomainException('No se pueden agregar series a una sesión finalizada');
        }

        // Find RoutineDayExercise
        $routineDayExercise = $this->routineDayExerciseRepository->findBySessionAndExercise(
            $sessionIdVO,
            $exerciseIdVO
        );

        if ($routineDayExercise === null) {
            throw new \DomainException('Este ejercicio no pertenece a la sesión actual');
        }

        // Guard: Set must not be already executed
        if ($this->setExecutionRepository->existsSetExecution($sessionIdVO, $exerciseIdVO, $setNumber)) {
            throw new \DomainException('Esta serie ya ha sido ejecutada');
        }

        // Create SetExecution
        $setExecution = new SetExecutionEntity(
            SetExecutionId::generate(),
            $sessionIdVO,
            $routineDayExercise->getId(),
            $exerciseIdVO,
            new SetNumber($setNumber),
            new RepsCompleted($repsCompleted),
            new WeightUsed($weightUsed),
            new \DateTimeImmutable()
        );

        $this->setExecutionRepository->save($setExecution);

        // Update weight history if weight was used
        if ($weightUsed !== null) {
            $this->updateWeightHistory(
                $session->getStudentId(),
                $exerciseIdVO,
                new Reps($repsCompleted),
                new Weight($weightUsed)
            );
        }

        return $setExecution;
    }

    private function updateWeightHistory(
        UserId $studentId,
        ExerciseId $exerciseId,
        Reps $reps,
        Weight $weight
    ): void {
        if (!$this->historyDomainService->shouldUpdateHistory($studentId, $exerciseId, $reps, $weight)) {
            return;
        }

        $existing = $this->historyRepository->findByStudentExerciseAndReps($studentId, $exerciseId, $reps);

        if ($existing !== null) {
            $existing->updateWeight($weight);
            $this->historyRepository->upsert($existing);
        } else {
            $newHistory = new ExerciseWeightHistoryEntity(
                ExerciseWeightHistoryId::generate(),
                $studentId,
                $exerciseId,
                $reps,
                $weight,
                new \DateTimeImmutable()
            );
            $this->historyRepository->upsert($newHistory);
        }
    }
}
