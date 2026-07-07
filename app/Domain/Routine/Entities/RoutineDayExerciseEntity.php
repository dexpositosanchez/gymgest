<?php

declare(strict_types=1);

namespace App\Domain\Routine\Entities;

use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Routine\ValueObjects\RoutineDayId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Routine\ValueObjects\OrderIndex;

class RoutineDayExerciseEntity
{
    /** @var RoutineDayExerciseId */
    private $id;

    /** @var RoutineDayId */
    private $routineDayId;

    /** @var ExerciseId */
    private $exerciseId;

    /** @var OrderIndex */
    private $orderIndex;

    /** @var ExerciseSetEntity[] */
    private $sets;

    /** @var string|null */
    private $notes;

    /** @var \DateTimeImmutable */
    private $createdAt;

    /** @var \DateTimeImmutable */
    private $updatedAt;

    /**
     * @param ExerciseSetEntity[] $sets
     */
    public function __construct(
        RoutineDayExerciseId $id,
        RoutineDayId $routineDayId,
        ExerciseId $exerciseId,
        OrderIndex $orderIndex,
        array $sets,
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->routineDayId = $routineDayId;
        $this->exerciseId = $exerciseId;
        $this->orderIndex = $orderIndex;
        $this->sets = $sets;
        $this->notes = $notes;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): RoutineDayExerciseId
    {
        return $this->id;
    }

    public function getRoutineDayId(): RoutineDayId
    {
        return $this->routineDayId;
    }

    public function getExerciseId(): ExerciseId
    {
        return $this->exerciseId;
    }

    public function getOrderIndex(): OrderIndex
    {
        return $this->orderIndex;
    }

    /**
     * @return ExerciseSetEntity[]
     */
    public function getSets(): array
    {
        return $this->sets;
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
