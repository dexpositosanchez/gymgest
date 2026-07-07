<?php

declare(strict_types=1);

namespace App\Domain\Routine\Entities;

use App\Domain\Routine\ValueObjects\ExerciseSetId;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Routine\ValueObjects\SetNumber;
use App\Domain\Routine\ValueObjects\Reps;

class ExerciseSetEntity
{
    /** @var ExerciseSetId */
    private $id;

    /** @var RoutineDayExerciseId */
    private $routineDayExerciseId;

    /** @var SetNumber */
    private $setNumber;

    /** @var Reps */
    private $reps;

    /** @var string|null */
    private $notes;

    /** @var \DateTimeImmutable */
    private $createdAt;

    /** @var \DateTimeImmutable */
    private $updatedAt;

    public function __construct(
        ExerciseSetId $id,
        RoutineDayExerciseId $routineDayExerciseId,
        SetNumber $setNumber,
        Reps $reps,
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->routineDayExerciseId = $routineDayExerciseId;
        $this->setNumber = $setNumber;
        $this->reps = $reps;
        $this->notes = $notes;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ExerciseSetId
    {
        return $this->id;
    }

    public function getRoutineDayExerciseId(): RoutineDayExerciseId
    {
        return $this->routineDayExerciseId;
    }

    public function getSetNumber(): SetNumber
    {
        return $this->setNumber;
    }

    public function getReps(): Reps
    {
        return $this->reps;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
