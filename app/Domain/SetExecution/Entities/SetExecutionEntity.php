<?php

declare(strict_types=1);

namespace App\Domain\SetExecution\Entities;

use App\Domain\SetExecution\ValueObjects\SetExecutionId;
use App\Domain\SetExecution\ValueObjects\SetNumber;
use App\Domain\SetExecution\ValueObjects\RepsCompleted;
use App\Domain\SetExecution\ValueObjects\WeightUsed;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseId;

class SetExecutionEntity
{
    /** @var SetExecutionId */
    private $id;

    /** @var WorkoutSessionId */
    private $workoutSessionId;

    /** @var RoutineDayExerciseId */
    private $routineDayExerciseId;

    /** @var ExerciseId */
    private $exerciseId;

    /** @var SetNumber */
    private $setNumber;

    /** @var RepsCompleted */
    private $repsCompleted;

    /** @var WeightUsed */
    private $weightUsed;

    /** @var \DateTimeImmutable */
    private $completedAt;

    public function __construct(
        SetExecutionId $id,
        WorkoutSessionId $workoutSessionId,
        RoutineDayExerciseId $routineDayExerciseId,
        ExerciseId $exerciseId,
        SetNumber $setNumber,
        RepsCompleted $repsCompleted,
        WeightUsed $weightUsed,
        \DateTimeImmutable $completedAt
    ) {
        $this->id = $id;
        $this->workoutSessionId = $workoutSessionId;
        $this->routineDayExerciseId = $routineDayExerciseId;
        $this->exerciseId = $exerciseId;
        $this->setNumber = $setNumber;
        $this->repsCompleted = $repsCompleted;
        $this->weightUsed = $weightUsed;
        $this->completedAt = $completedAt;
    }

    public function getId(): SetExecutionId
    {
        return $this->id;
    }

    public function getWorkoutSessionId(): WorkoutSessionId
    {
        return $this->workoutSessionId;
    }

    public function getRoutineDayExerciseId(): RoutineDayExerciseId
    {
        return $this->routineDayExerciseId;
    }

    public function getExerciseId(): ExerciseId
    {
        return $this->exerciseId;
    }

    public function getSetNumber(): SetNumber
    {
        return $this->setNumber;
    }

    public function getRepsCompleted(): RepsCompleted
    {
        return $this->repsCompleted;
    }

    public function getWeightUsed(): WeightUsed
    {
        return $this->weightUsed;
    }

    public function getCompletedAt(): \DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function hasWeight(): bool
    {
        return $this->weightUsed->getValue() !== null;
    }
}
