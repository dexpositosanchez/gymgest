<?php

declare(strict_types=1);

namespace App\Application\Exercise\DTOs;

class UpdateExerciseDTO
{
    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var string */
    public $muscleGroupId;

    public function __construct(string $name, string $description, string $muscleGroupId)
    {
        $this->name = $name;
        $this->description = $description;
        $this->muscleGroupId = $muscleGroupId;
    }
}
