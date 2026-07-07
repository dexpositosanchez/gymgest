<?php

declare(strict_types=1);

namespace App\Application\Routine\DTOs;

class RoutineResponseDTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string|null */
    public $description;

    /** @var string */
    public $difficulty;

    /** @var array */
    public $days;

    /** @var bool */
    public $is_assigned;

    /** @var string */
    public $created_at;

    /** @var string */
    public $updated_at;

    public function __construct(
        string $id,
        string $name,
        ?string $description,
        string $difficulty,
        array $days,
        bool $isAssigned,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->difficulty = $difficulty;
        $this->days = $days;
        $this->is_assigned = $isAssigned;
        $this->created_at = $createdAt;
        $this->updated_at = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'difficulty' => $this->difficulty,
            'days' => $this->days,
            'is_assigned' => $this->is_assigned,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
