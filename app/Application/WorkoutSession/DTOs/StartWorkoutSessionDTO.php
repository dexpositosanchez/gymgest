<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\DTOs;

class StartWorkoutSessionDTO
{
    /** @var string */
    public $studentId;

    /** @var string */
    public $routineAssignmentId;

    /** @var int */
    public $dayNumber;

    /** @var string|null */
    public $notes;

    public function __construct(
        string $studentId,
        string $routineAssignmentId,
        int $dayNumber,
        ?string $notes
    ) {
        $this->studentId = $studentId;
        $this->routineAssignmentId = $routineAssignmentId;
        $this->dayNumber = $dayNumber;
        $this->notes = $notes;
    }
}
