<?php

declare(strict_types=1);

namespace App\Application\Exercise\DTOs;

class ExerciseResponseDTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var MuscleGroupResponseDTO */
    public $muscleGroup;

    /** @var string */
    public $type;

    /** @var bool */
    public $isActive;

    /** @var string */
    public $createdAt;

    public function __construct(
        string $id,
        string $name,
        string $description,
        MuscleGroupResponseDTO $muscleGroup,
        string $type,
        bool $isActive,
        string $createdAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->muscleGroup = $muscleGroup;
        $this->type = $type;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'muscle_group' => $this->muscleGroup->toArray(),
            'type' => $this->type,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
        ];
    }
}
