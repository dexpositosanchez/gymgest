<?php

declare(strict_types=1);

namespace App\Application\Routine\DTOs;

class UpdateRoutineDTO
{
    /** @var string */
    public $name;

    /** @var string|null */
    public $description;

    /** @var string */
    public $difficulty;

    /** @var array */
    public $days;

    public function __construct(string $name, ?string $description, string $difficulty, array $days)
    {
        $this->name = $name;
        $this->description = $description;
        $this->difficulty = $difficulty;
        $this->days = $days;
    }
}
