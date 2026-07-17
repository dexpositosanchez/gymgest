<?php

declare(strict_types=1);

namespace App\Domain\Routine\Entities;

use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\RoutineName;
use App\Domain\Routine\ValueObjects\RoutineDescription;
use App\Domain\Routine\ValueObjects\RoutineDifficulty;
use App\Domain\User\ValueObjects\UserId;

class RoutineEntity
{
    /** @var RoutineId */
    private $id;

    /** @var UserId */
    private $trainerId;

    /** @var RoutineName */
    private $name;

    /** @var RoutineDescription|null */
    private $description;

    /** @var RoutineDifficulty */
    private $difficulty;

    /** @var RoutineDayEntity[] */
    private $days;

    /** @var \DateTimeImmutable */
    private $createdAt;

    /** @var \DateTimeImmutable */
    private $updatedAt;

    public function __construct(
        RoutineId $id,
        UserId $trainerId,
        RoutineName $name,
        ?RoutineDescription $description,
        RoutineDifficulty $difficulty,
        array $days = []
    ) {
        $this->id = $id;
        $this->trainerId = $trainerId;
        $this->name = $name;
        $this->description = $description;
        $this->difficulty = $difficulty;
        $this->days = $days;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): RoutineId
    {
        return $this->id;
    }

    public function getTrainerId(): UserId
    {
        return $this->trainerId;
    }

    public function getName(): RoutineName
    {
        return $this->name;
    }

    public function getDescription(): ?RoutineDescription
    {
        return $this->description;
    }

    public function getDifficulty(): RoutineDifficulty
    {
        return $this->difficulty;
    }

    public function getDays(): array
    {
        return $this->days;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isAssigned(): bool
    {
        // TODO: Check routine_assignments table when implemented
        return false;
    }

    public function belongsToTrainer(UserId $trainerId): bool
    {
        return $this->trainerId->equals($trainerId);
    }

    public function updateDetails(
        RoutineName $name,
        ?RoutineDescription $description,
        RoutineDifficulty $difficulty
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->difficulty = $difficulty;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setDays(array $days): void
    {
        $this->days = $days;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function hasDayNumber(int $dayNumber): bool
    {
        foreach ($this->days as $day) {
            if ($day->getDayNumber()->getValue() === $dayNumber) {
                return true;
            }
        }
        return false;
    }
}
