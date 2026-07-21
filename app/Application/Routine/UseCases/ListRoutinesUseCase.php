<?php

declare(strict_types=1);

namespace App\Application\Routine\UseCases;

use App\Application\Routine\DTOs\RoutineResponseDTO;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;

class ListRoutinesUseCase
{
    /** @var RoutineRepositoryInterface */
    private $routineRepository;

    /** @var ExerciseRepositoryInterface */
    private $exerciseRepository;

    public function __construct(
        RoutineRepositoryInterface $routineRepository,
        ExerciseRepositoryInterface $exerciseRepository
    ) {
        $this->routineRepository = $routineRepository;
        $this->exerciseRepository = $exerciseRepository;
    }

    /**
     * @param UserId $trainerId
     * @param array $filters
     * @return RoutineResponseDTO[]
     */
    public function execute(UserId $trainerId, array $filters = []): array
    {
        $routines = $this->routineRepository->findByTrainer($trainerId, $filters);

        $responseList = [];
        foreach ($routines as $routine) {
            // Construir array de días para la respuesta
            $daysArray = [];
            foreach ($routine->getDays() as $day) {
                $exercisesArray = [];
                $muscleGroupsSet = [];

                foreach ($day->getExercises() as $exercise) {
                    $setsArray = [];
                    foreach ($exercise->getSets() as $set) {
                        $setsArray[] = [
                            'id' => $set->getId()->getValue(),
                            'set_number' => $set->getSetNumber()->getValue(),
                            'reps' => $set->getReps()->getValue(),
                            'notes' => $set->getNotes(),
                        ];
                    }

                    $exercisesArray[] = [
                        'id' => $exercise->getId()->getValue(),
                        'exercise_id' => $exercise->getExerciseId()->getValue(),
                        'order_index' => $exercise->getOrderIndex()->getValue(),
                        'sets' => $setsArray,
                        'notes' => $exercise->getNotes(),
                    ];

                    // Recopilar nombre del grupo muscular mediante repositorio
                    $muscleGroupName = $this->exerciseRepository->getMuscleGroupName($exercise->getExerciseId());
                    if ($muscleGroupName) {
                        $muscleGroupsSet[$muscleGroupName] = true;
                    }
                }

                $daysArray[] = [
                    'id' => $day->getId()->getValue(),
                    'day_number' => $day->getDayNumber()->getValue(),
                    'name' => $day->getName()->getValue(),
                    'muscle_groups' => array_keys($muscleGroupsSet),
                    'exercises' => $exercisesArray,
                ];
            }

            $responseList[] = new RoutineResponseDTO(
                $routine->getId()->getValue(),
                $routine->getName()->getValue(),
                $routine->getDescription() ? $routine->getDescription()->getValue() : null,
                $routine->getDifficulty()->getValue(),
                $daysArray,
                $this->routineRepository->hasAssignments($routine->getId()),
                $routine->getCreatedAt()->format('Y-m-d H:i:s'),
                $routine->getUpdatedAt()->format('Y-m-d H:i:s')
            );
        }

        return $responseList;
    }
}
