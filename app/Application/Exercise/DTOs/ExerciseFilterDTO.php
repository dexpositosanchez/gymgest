<?php

declare(strict_types=1);

namespace App\Application\Exercise\DTOs;

class ExerciseFilterDTO
{
    /** @var string|null */
    public $muscleGroupId;

    /** @var string|null */
    public $search;

    /** @var bool */
    public $includeInactive;

    /** @var string|null */
    public $type;

    public function __construct(?string $muscleGroupId = null, ?string $search = null, bool $includeInactive = false, ?string $type = null)
    {
        $this->muscleGroupId = $muscleGroupId;
        $this->search = $search;
        $this->includeInactive = $includeInactive;
        $this->type = $type;
    }
}
