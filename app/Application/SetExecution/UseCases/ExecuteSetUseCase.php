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
use App\Domain\Routine\Repositories\ExerciseSetRepositoryInterface;
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

    /** @var ExerciseSetRepositoryInterface */
    private $exerciseSetRepository;

    /** @var ExerciseWeightHistoryRepositoryInterface */
    private $historyRepository;

    /** @var WeightHistoryDomainService */
    private $historyDomainService;

    public function __construct(
        SetExecutionRepositoryInterface $setExecutionRepository,
        WorkoutSessionRepositoryInterface $sessionRepository,
        RoutineDayExerciseRepositoryInterface $routineDayExerciseRepository,
        ExerciseSetRepositoryInterface $exerciseSetRepository,
        ExerciseWeightHistoryRepositoryInterface $historyRepository,
        WeightHistoryDomainService $historyDomainService
    ) {
        $this->setExecutionRepository = $setExecutionRepository;
        $this->sessionRepository = $sessionRepository;
        $this->routineDayExerciseRepository = $routineDayExerciseRepository;
        $this->exerciseSetRepository = $exerciseSetRepository;
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

        // Verificar: la sesión debe existir
        $session = $this->sessionRepository->findById($sessionIdVO);
        if ($session === null) {
            throw new \DomainException('Sesión no encontrada');
        }

        // Verificar: la sesión debe permitir agregar series
        if (!$session->canAddSets()) {
            throw new \DomainException('No se pueden agregar series a una sesión finalizada');
        }

        // Buscar RoutineDayExercise
        $routineDayExercise = $this->routineDayExerciseRepository->findBySessionAndExercise(
            $sessionIdVO,
            $exerciseIdVO
        );

        if ($routineDayExercise === null) {
            throw new \DomainException('Este ejercicio no pertenece a la sesión actual');
        }

        // Verificar: el número de serie debe existir en la configuración del ejercicio
        $exerciseSets = $this->exerciseSetRepository->findByRoutineDayExerciseId(
            $routineDayExercise->getId()
        );

        $setNumberExists = false;
        $configuredReps = null;
        foreach ($exerciseSets as $exerciseSet) {
            if ($exerciseSet->getSetNumber()->getValue() === $setNumber) {
                $setNumberExists = true;
                $configuredReps = $exerciseSet->getReps()->getValue();
                break;
            }
        }

        if (!$setNumberExists) {
            throw new \DomainException('El número de serie no existe en la configuración del ejercicio');
        }

        // Verificar: las repeticiones completadas deben coincidir con las configuradas
        if ($repsCompleted !== $configuredReps) {
            throw new \DomainException(
                sprintf(
                    'Las repeticiones completadas (%d) no coinciden con las configuradas (%d)',
                    $repsCompleted,
                    $configuredReps
                )
            );
        }

        // Verificar: la serie no debe estar ya ejecutada
        if ($this->setExecutionRepository->existsSetExecution($sessionIdVO, $exerciseIdVO, $setNumber)) {
            throw new \DomainException('Esta serie ya ha sido ejecutada');
        }

        // Crear SetExecution
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

        // Actualizar historial de pesos solo si se utilizó peso Y si el peso cambió
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
        // Buscar registro existente (UNA SOLA VEZ)
        $existing = $this->historyRepository->findByStudentExerciseAndReps($studentId, $exerciseId, $reps);

        if ($existing !== null) {
            // Solo actualizar si el peso cambió
            if ($existing->shouldUpdate($weight)) {
                $existing->updateWeight($weight);
                $this->historyRepository->upsert($existing);
            }
            // Si el peso es igual, no hacer nada (no actualizar timestamp ni crear duplicado)
        } else {
            // No existe historial, crear nuevo
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
