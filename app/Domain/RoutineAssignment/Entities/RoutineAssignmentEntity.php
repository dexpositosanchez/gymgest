<?php

declare(strict_types=1);

namespace App\Domain\RoutineAssignment\Entities;

use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\RoutineAssignment\ValueObjects\AssignedAt;
use App\Domain\RoutineAssignment\ValueObjects\StartsAt;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Gym\ValueObjects\GymId;

final class RoutineAssignmentEntity
{
    private $id;
    private $routineId;
    private $studentId;
    private $gymId;
    private $assignedAt;
    private $startsAt;
    private $isCurrent;
    private $notes;

    public function __construct(
        RoutineAssignmentId $id,
        RoutineId $routineId,
        UserId $studentId,
        GymId $gymId,
        AssignedAt $assignedAt,
        StartsAt $startsAt,
        bool $isCurrent,
        ?string $notes = null
    ) {
        $this->id = $id;
        $this->routineId = $routineId;
        $this->studentId = $studentId;
        $this->gymId = $gymId;
        $this->assignedAt = $assignedAt;
        $this->startsAt = $startsAt;
        $this->isCurrent = $isCurrent;
        $this->notes = $notes;
    }

    public function getId(): RoutineAssignmentId
    {
        return $this->id;
    }

    public function getRoutineId(): RoutineId
    {
        return $this->routineId;
    }

    public function getStudentId(): UserId
    {
        return $this->studentId;
    }

    public function getGymId(): GymId
    {
        return $this->gymId;
    }

    public function getAssignedAt(): AssignedAt
    {
        return $this->assignedAt;
    }

    public function getStartsAt(): StartsAt
    {
        return $this->startsAt;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setAsCurrent(): void
    {
        $this->isCurrent = true;
    }

    public function unsetAsCurrent(): void
    {
        $this->isCurrent = false;
    }

    public function belongsToStudent(UserId $studentId): bool
    {
        return $this->studentId->equals($studentId);
    }

    public function belongsToGym(GymId $gymId): bool
    {
        return $this->gymId->equals($gymId);
    }

    public function updateNotes(string $notes): void
    {
        $this->notes = $notes;
    }

    public function updateStartsAt(StartsAt $startsAt): void
    {
        $this->startsAt = $startsAt;
    }
}
