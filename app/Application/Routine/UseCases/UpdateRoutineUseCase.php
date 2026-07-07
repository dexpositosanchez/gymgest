<?php

declare(strict_types=1);

namespace App\Application\Routine\UseCases;

use App\Application\Routine\DTOs\UpdateRoutineDTO;
use App\Domain\Routine\Entities\RoutineEntity;
use App\Domain\Routine\Entities\RoutineDayEntity;
use App\Domain\Routine\Entities\RoutineDayExerciseEntity;
use App\Domain\Routine\Entities\ExerciseSetEntity;
use App\Domain\Routine\Repositories\RoutineRepositoryInterface;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\RoutineName;
use App\Domain\Routine\ValueObjects\RoutineDescription;
use App\Domain\Routine\ValueObjects\RoutineDifficulty;
use App\Domain\Routine\ValueObjects\RoutineDayId;
use App\Domain\Routine\ValueObjects\DayNumber;
use App\Domain\Routine\ValueObjects\DayName;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Routine\ValueObjects\OrderIndex;
use App\Domain\Routine\ValueObjects\ExerciseSetId;
use App\Domain\Routine\ValueObjects\SetNumber;
use App\Domain\Routine\ValueObjects\Reps;
use App\Domain\User\ValueObjects\UserId;

class UpdateRoutineUseCase
{
    /** @var RoutineRepositoryInterface */
    private $routineRepository;

    public function __construct(RoutineRepositoryInterface $routineRepository)
    {
        $this->routineRepository = $routineRepository;
    }

    public function execute(RoutineId $routineId, UpdateRoutineDTO $dto, UserId $trainerId): RoutineEntity
    {
        $routine = $this->routineRepository->findById($routineId);

        if (!$routine) {
            throw new \DomainException('Rutina no encontrada');
        }

        // Verify routine belongs to trainer
        if (!$routine->belongsToTrainer($trainerId)) {
            throw new \DomainException('No tienes permiso para editar esta rutina');
        }

        // Check if routine is assigned
        if ($routine->isAssigned()) {
            throw new \DomainException('No se puede editar una rutina asignada a estudiantes');
        }

        // Validate at least one day
        if (empty($dto->days)) {
            throw new \DomainException('La rutina debe tener al menos un día');
        }

        // Update basic details
        $routine->updateDetails(
            new RoutineName($dto->name),
            $dto->description ? new RoutineDescription($dto->description) : null,
            RoutineDifficulty::fromString($dto->difficulty)
        );

        // Recreate days with exercises
        $days = [];
        foreach ($dto->days as $dayData) {
            // Validate at least one exercise per day
            if (empty($dayData['exercises'])) {
                throw new \DomainException('Cada día debe tener al menos un ejercicio');
            }

            $dayId = RoutineDayId::generate();

            $exercises = [];
            foreach ($dayData['exercises'] as $exerciseData) {
                // Validate at least one set per exercise
                if (empty($exerciseData['sets'])) {
                    throw new \DomainException('Cada ejercicio debe tener al menos una serie');
                }

                $exerciseId = RoutineDayExerciseId::generate();

                $sets = [];
                foreach ($exerciseData['sets'] as $setData) {
                    $sets[] = new ExerciseSetEntity(
                        ExerciseSetId::generate(),
                        $exerciseId,
                        new SetNumber($setData['set_number']),
                        new Reps($setData['reps']),
                        $setData['notes'] ?? null
                    );
                }

                $exercises[] = new RoutineDayExerciseEntity(
                    $exerciseId,
                    $dayId,
                    new ExerciseId($exerciseData['exercise_id']),
                    new OrderIndex($exerciseData['order_index']),
                    $sets,
                    $exerciseData['notes'] ?? null
                );
            }

            $days[] = new RoutineDayEntity(
                $dayId,
                $routine->getId(),
                new DayNumber($dayData['day_number']),
                new DayName($dayData['name']),
                $exercises
            );
        }

        $routine->setDays($days);

        $this->routineRepository->save($routine);

        return $routine;
    }
}
