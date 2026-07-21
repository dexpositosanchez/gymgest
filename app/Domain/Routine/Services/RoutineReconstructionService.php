<?php

declare(strict_types=1);

namespace App\Domain\Routine\Services;

use App\Domain\Routine\Entities\RoutineDayEntity;
use App\Domain\Routine\Entities\RoutineDayExerciseEntity;
use App\Domain\Routine\Entities\ExerciseSetEntity;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\RoutineDayId;
use App\Domain\Routine\ValueObjects\DayNumber;
use App\Domain\Routine\ValueObjects\DayName;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Routine\ValueObjects\OrderIndex;
use App\Domain\Routine\ValueObjects\ExerciseSetId;
use App\Domain\Routine\ValueObjects\SetNumber;
use App\Domain\Routine\ValueObjects\Reps;

/**
 * Servicio de dominio para reconstruir estructura de rutinas desde DTOs
 * Elimina duplicación de código entre CreateRoutineUseCase y UpdateRoutineUseCase
 */
class RoutineReconstructionService
{
    /**
     * Reconstruir días de rutina desde datos de array
     *
     * @param array $daysData Array de datos de días desde DTO
     * @param RoutineId $routineId La rutina a la que pertenecen estos días
     * @return RoutineDayEntity[]
     * @throws \DomainException
     */
    public function reconstructDays(array $daysData, RoutineId $routineId): array
    {
        if (empty($daysData)) {
            throw new \DomainException('La rutina debe tener al menos un día');
        }

        $days = [];
        foreach ($daysData as $dayData) {
            $days[] = $this->reconstructDay($dayData, $routineId);
        }

        return $days;
    }

    /**
     * Reconstruir un único día con sus ejercicios
     *
     * @param array $dayData
     * @param RoutineId $routineId
     * @return RoutineDayEntity
     * @throws \DomainException
     */
    private function reconstructDay(array $dayData, RoutineId $routineId): RoutineDayEntity
    {
        // Validar al menos un ejercicio por día
        if (empty($dayData['exercises'])) {
            throw new \DomainException('Cada día debe tener al menos un ejercicio');
        }

        $dayId = RoutineDayId::generate();

        $exercises = [];
        foreach ($dayData['exercises'] as $exerciseData) {
            $exercises[] = $this->reconstructExercise($exerciseData, $dayId);
        }

        return new RoutineDayEntity(
            $dayId,
            $routineId,
            new DayNumber($dayData['day_number']),
            new DayName($dayData['name']),
            $exercises
        );
    }

    /**
     * Reconstruir un único ejercicio con sus series
     *
     * @param array $exerciseData
     * @param RoutineDayId $dayId
     * @return RoutineDayExerciseEntity
     * @throws \DomainException
     */
    private function reconstructExercise(array $exerciseData, RoutineDayId $dayId): RoutineDayExerciseEntity
    {
        // Validar al menos una serie por ejercicio
        if (empty($exerciseData['sets'])) {
            throw new \DomainException('Cada ejercicio debe tener al menos una serie');
        }

        $exerciseId = RoutineDayExerciseId::generate();

        $sets = [];
        foreach ($exerciseData['sets'] as $setData) {
            $sets[] = $this->reconstructSet($setData, $exerciseId);
        }

        return new RoutineDayExerciseEntity(
            $exerciseId,
            $dayId,
            new ExerciseId($exerciseData['exercise_id']),
            new OrderIndex($exerciseData['order_index']),
            $sets,
            $exerciseData['notes'] ?? null
        );
    }

    /**
     * Reconstruir una única serie
     *
     * @param array $setData
     * @param RoutineDayExerciseId $exerciseId
     * @return ExerciseSetEntity
     */
    private function reconstructSet(array $setData, RoutineDayExerciseId $exerciseId): ExerciseSetEntity
    {
        return new ExerciseSetEntity(
            ExerciseSetId::generate(),
            $exerciseId,
            new SetNumber($setData['set_number']),
            new Reps($setData['reps']),
            $setData['notes'] ?? null
        );
    }
}
