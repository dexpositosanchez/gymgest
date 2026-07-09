<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\RoutineAssignment\Entities;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity;
use App\Domain\RoutineAssignment\ValueObjects\AssignedAt;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\RoutineAssignment\ValueObjects\StartsAt;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class RoutineAssignmentEntityTest extends TestCase
{
    private function createRoutineAssignment(
        bool $isCurrent = true,
        ?string $notes = null
    ): RoutineAssignmentEntity {
        return new RoutineAssignmentEntity(
            new RoutineAssignmentId('123e4567-e89b-12d3-a456-426614174000'),
            new RoutineId('223e4567-e89b-12d3-a456-426614174000'),
            new UserId('323e4567-e89b-12d3-a456-426614174000'),
            new GymId('423e4567-e89b-12d3-a456-426614174000'),
            AssignedAt::now(),
            StartsAt::fromString(date('Y-m-d')),
            $isCurrent,
            $notes
        );
    }

    public function test_can_create_routine_assignment(): void
    {
        $assignment = $this->createRoutineAssignment();

        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $assignment->getId()->getValue());
        $this->assertEquals('223e4567-e89b-12d3-a456-426614174000', $assignment->getRoutineId()->getValue());
        $this->assertEquals('323e4567-e89b-12d3-a456-426614174000', $assignment->getStudentId()->getValue());
        $this->assertEquals('423e4567-e89b-12d3-a456-426614174000', $assignment->getGymId()->getValue());
        $this->assertTrue($assignment->isCurrent());
        $this->assertNull($assignment->getNotes());
    }

    public function test_can_set_as_current(): void
    {
        $assignment = $this->createRoutineAssignment(false);

        $assignment->setAsCurrent();

        $this->assertTrue($assignment->isCurrent());
    }

    public function test_can_unset_as_current(): void
    {
        $assignment = $this->createRoutineAssignment(true);

        $assignment->unsetAsCurrent();

        $this->assertFalse($assignment->isCurrent());
    }

    public function test_is_current_returns_correct_boolean(): void
    {
        $currentAssignment = $this->createRoutineAssignment(true);
        $notCurrentAssignment = $this->createRoutineAssignment(false);

        $this->assertTrue($currentAssignment->isCurrent());
        $this->assertFalse($notCurrentAssignment->isCurrent());
    }

    public function test_belongs_to_student(): void
    {
        $studentId = new UserId('323e4567-e89b-12d3-a456-426614174000');
        $assignment = $this->createRoutineAssignment();

        $this->assertTrue($assignment->belongsToStudent($studentId));
    }

    public function test_belongs_to_gym(): void
    {
        $gymId = new GymId('423e4567-e89b-12d3-a456-426614174000');
        $assignment = $this->createRoutineAssignment();

        $this->assertTrue($assignment->belongsToGym($gymId));
    }

    public function test_can_update_notes(): void
    {
        $assignment = $this->createRoutineAssignment();
        $newNotes = 'Nueva rutina de hipertrofia';

        $assignment->updateNotes($newNotes);

        $this->assertEquals($newNotes, $assignment->getNotes());
    }

    public function test_can_update_starts_at(): void
    {
        $assignment = $this->createRoutineAssignment();
        $newStartsAt = StartsAt::fromString('2026-09-01');

        $assignment->updateStartsAt($newStartsAt);

        $this->assertEquals('2026-09-01', $assignment->getStartsAt()->getValue());
    }
}
