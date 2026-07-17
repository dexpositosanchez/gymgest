<?php

declare(strict_types=1);

namespace App\Application\SetExecution\UseCases;

use App\Domain\SetExecution\Repositories\SetExecutionRepositoryInterface;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\ExerciseWeightHistory\Repositories\ExerciseWeightHistoryRepositoryInterface;
use App\Domain\Routine\Repositories\ExerciseSetRepositoryInterface;
use App\Domain\Routine\Repositories\RoutineDayExerciseRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class GetExerciseSetsUseCase
{
    /** @var SetExecutionRepositoryInterface */
    private $setExecutionRepository;

    /** @var ExerciseSetRepositoryInterface */
    private $exerciseSetRepository;

    /** @var RoutineDayExerciseRepositoryInterface */
    private $routineDayExerciseRepository;

    /** @var ExerciseWeightHistoryRepositoryInterface */
    private $historyRepository;

    public function __construct(
        SetExecutionRepositoryInterface $setExecutionRepository,
        ExerciseSetRepositoryInterface $exerciseSetRepository,
        RoutineDayExerciseRepositoryInterface $routineDayExerciseRepository,
        ExerciseWeightHistoryRepositoryInterface $historyRepository
    ) {
        $this->setExecutionRepository = $setExecutionRepository;
        $this->exerciseSetRepository = $exerciseSetRepository;
        $this->routineDayExerciseRepository = $routineDayExerciseRepository;
        $this->historyRepository = $historyRepository;
    }

    /**
     * @param string $sessionId
     * @param string $exerciseId
     * @param string $studentId
     * @return array{sets: array<int, array{set_number: int, reps: int, suggested_weight: float|null, is_completed: bool}>, total_sets: int, completed_sets: int}
     */
    public function execute(string $sessionId, string $exerciseId, string $studentId): array
    {
        $sessionIdVO = new WorkoutSessionId($sessionId);
        $exerciseIdVO = new ExerciseId($exerciseId);
        $studentIdVO = new UserId($studentId);

        // Find RoutineDayExercise
        $routineDayExercise = $this->routineDayExerciseRepository->findBySessionAndExercise(
            $sessionIdVO,
            $exerciseIdVO
        );

        if ($routineDayExercise === null) {
            throw new \DomainException('Este ejercicio no pertenece a la sesión actual');
        }

        // Get all sets configured for this exercise
        $exerciseSets = $this->exerciseSetRepository->findByRoutineDayExerciseId(
            $routineDayExercise->getId()
        );

        // Get completed sets
        $completedSets = $this->setExecutionRepository->findBySessionAndExercise($sessionIdVO, $exerciseIdVO);
        $completedSetNumbers = array_map(
            fn($set) => $set->getSetNumber()->getValue(),
            $completedSets
        );

        // Get suggested weight
        $suggestedWeight = $this->historyRepository->findSuggestedWeight($studentIdVO, $exerciseIdVO);

        $sets = [];
        foreach ($exerciseSets as $exerciseSet) {
            $setNumber = $exerciseSet->getSetNumber()->getValue();
            $sets[] = [
                'set_number' => $setNumber,
                'reps' => $exerciseSet->getReps()->getValue(),
                'suggested_weight' => $suggestedWeight,
                'is_completed' => in_array($setNumber, $completedSetNumbers),
            ];
        }

        return [
            'sets' => $sets,
            'total_sets' => count($exerciseSets),
            'completed_sets' => count($completedSets),
        ];
    }
}
