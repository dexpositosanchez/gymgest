<?php

declare(strict_types=1);

namespace App\Domain\Routine\Entities;

use App\Domain\Routine\ValueObjects\RoutineDayId;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\DayNumber;
use App\Domain\Routine\ValueObjects\DayName;

class RoutineDayEntity
{
    /** @var RoutineDayId */
    private $id;

    /** @var RoutineId */
    private $routineId;

    /** @var DayNumber */
    private $dayNumber;

    /** @var DayName */
    private $name;

    /** @var RoutineDayExerciseEntity[] */
    private $exercises;

    /** @var \DateTimeImmutable */
    private $createdAt;

    /** @var \DateTimeImmutable */
    private $updatedAt;

    public function __construct(
        RoutineDayId $id,
        RoutineId $routineId,
        DayNumber $dayNumber,
        DayName $name,
        array $exercises = []
    ) {
        $this->id = $id;
        $this->routineId = $routineId;
        $this->dayNumber = $dayNumber;
        $this->name = $name;
        $this->exercises = $exercises;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): RoutineDayId
    {
        return $this->id;
    }

    public function getRoutineId(): RoutineId
    {
        return $this->routineId;
    }

    public function getDayNumber(): DayNumber
    {
        return $this->dayNumber;
    }

    public function getName(): DayName
    {
        return $this->name;
    }

    public function getExercises(): array
    {
        return $this->exercises;
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
