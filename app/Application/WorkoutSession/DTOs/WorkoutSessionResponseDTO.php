<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\DTOs;

class WorkoutSessionResponseDTO
{
    /** @var string */
    public $id;

    /** @var string */
    public $routineAssignmentId;

    /** @var int */
    public $dayNumber;

    /** @var string */
    public $startedAt;

    /** @var string|null */
    public $finishedAt;

    /** @var bool */
    public $isActive;

    /** @var string|null */
    public $notes;

    public function __construct(
        string $id,
        string $routineAssignmentId,
        int $dayNumber,
        string $startedAt,
        ?string $finishedAt,
        bool $isActive,
        ?string $notes
    ) {
        $this->id = $id;
        $this->routineAssignmentId = $routineAssignmentId;
        $this->dayNumber = $dayNumber;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;
        $this->isActive = $isActive;
        $this->notes = $notes;
    }
}
