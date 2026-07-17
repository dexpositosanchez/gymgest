<?php

declare(strict_types=1);

namespace App\Domain\WorkoutSession\Entities;

use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Routine\ValueObjects\DayNumber;

class WorkoutSessionEntity
{
    /** @var WorkoutSessionId */
    private $id;

    /** @var RoutineAssignmentId */
    private $routineAssignmentId;

    /** @var UserId */
    private $studentId;

    /** @var DayNumber */
    private $dayNumber;

    /** @var \DateTimeImmutable */
    private $startedAt;

    /** @var \DateTimeImmutable|null */
    private $finishedAt;

    /** @var bool */
    private $isActive;

    /** @var string|null */
    private $notes;

    public function __construct(
        WorkoutSessionId $id,
        RoutineAssignmentId $routineAssignmentId,
        UserId $studentId,
        DayNumber $dayNumber,
        \DateTimeImmutable $startedAt,
        ?\DateTimeImmutable $finishedAt,
        bool $isActive,
        ?string $notes
    ) {
        $this->id = $id;
        $this->routineAssignmentId = $routineAssignmentId;
        $this->studentId = $studentId;
        $this->dayNumber = $dayNumber;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;
        $this->isActive = $isActive;
        $this->notes = $notes;
    }

    public function getId(): WorkoutSessionId
    {
        return $this->id;
    }

    public function getRoutineAssignmentId(): RoutineAssignmentId
    {
        return $this->routineAssignmentId;
    }

    public function getStudentId(): UserId
    {
        return $this->studentId;
    }

    public function getDayNumber(): DayNumber
    {
        return $this->dayNumber;
    }

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function isFinished(): bool
    {
        return $this->finishedAt !== null;
    }

    public function canAddSets(): bool
    {
        return $this->isActive && !$this->isFinished();
    }

    public function finish(?string $notes = null): void
    {
        if ($this->isFinished()) {
            throw new \DomainException('La sesión ya está finalizada');
        }

        $this->finishedAt = new \DateTimeImmutable();
        $this->isActive = false;

        if ($notes !== null) {
            $this->notes = $notes;
        }
    }
}
