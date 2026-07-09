<?php

declare(strict_types=1);

namespace App\Application\Routine\UseCases;

use App\Application\Routine\DTOs\RoutineResponseDTO;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\User\ValueObjects\UserId;

class GetRoutineDetailsUseCase
{
    /** @var RoutineRepositoryInterface */
    private $routineRepository;

    public function __construct(RoutineRepositoryInterface $routineRepository)
    {
        $this->routineRepository = $routineRepository;
    }

    public function execute(RoutineId $routineId, UserId $trainerId): ?RoutineResponseDTO
    {
        $routine = $this->routineRepository->findById($routineId);

        if (!$routine) {
            return null;
        }

        // Verify routine belongs to trainer
        if (!$routine->belongsToTrainer($trainerId)) {
            throw new \DomainException('No tienes permiso para ver esta rutina');
        }

        // Build days array for response
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

                // Collect muscle group name (will be loaded via eager loading)
                $exerciseModel = \App\Infrastructure\Persistence\Eloquent\ExerciseEloquentModel::with('muscleGroup')->find($exercise->getExerciseId()->getValue());
                if ($exerciseModel && $exerciseModel->muscleGroup) {
                    $muscleGroupsSet[$exerciseModel->muscleGroup->name] = true;
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

        return new RoutineResponseDTO(
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
}
