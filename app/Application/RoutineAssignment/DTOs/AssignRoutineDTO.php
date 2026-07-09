<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\DTOs;

final class AssignRoutineDTO
{
    public $routineId;
    public $studentId;
    public $gymId;
    public $startsAt;
    public $isCurrent;
    public $notes;

    public function __construct(
        string $routineId,
        string $studentId,
        string $gymId,
        string $startsAt,
        bool $isCurrent = true,
        ?string $notes = null
    ) {
        $this->routineId = $routineId;
        $this->studentId = $studentId;
        $this->gymId = $gymId;
        $this->startsAt = $startsAt;
        $this->isCurrent = $isCurrent;
        $this->notes = $notes;
    }
}
