<?php

declare(strict_types=1);

namespace App\Application\Exercise\DTOs;

class MuscleGroupResponseDTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string|null */
    public $description;

    public function __construct(string $id, string $name, ?string $description = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
