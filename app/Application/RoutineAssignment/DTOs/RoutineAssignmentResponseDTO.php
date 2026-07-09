<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\DTOs;

final class RoutineAssignmentResponseDTO
{
    public $id;
    public $routineId;
    public $routineName;
    public $studentId;
    public $studentName;
    public $gymId;
    public $gymName;
    public $assignedAt;
    public $startsAt;
    public $isCurrent;
    public $notes;

    public function __construct(
        string $id,
        string $routineId,
        string $routineName,
        string $studentId,
        string $studentName,
        string $gymId,
        string $gymName,
        string $assignedAt,
        string $startsAt,
        bool $isCurrent,
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->routineId = $routineId;
        $this->routineName = $routineName;
        $this->studentId = $studentId;
        $this->studentName = $studentName;
        $this->gymId = $gymId;
        $this->gymName = $gymName;
        $this->assignedAt = $assignedAt;
        $this->startsAt = $startsAt;
        $this->isCurrent = $isCurrent;
        $this->notes = $notes;
    }
}
