<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\WorkoutSession\Entities;

use App\Domain\WorkoutSession\Entities\WorkoutSessionEntity;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Routine\ValueObjects\DayNumber;
use PHPUnit\Framework\TestCase;

class WorkoutSessionEntityTest extends TestCase
{
    private function createSession(bool $finished = false): WorkoutSessionEntity
    {
        $session = new WorkoutSessionEntity(
            new WorkoutSessionId('550e8400-e29b-41d4-a716-446655440000'),
            new RoutineAssignmentId('660e8400-e29b-41d4-a716-446655440000'),
            new UserId('770e8400-e29b-41d4-a716-446655440000'),
            new DayNumber(1),
            new \DateTimeImmutable('2026-07-15 10:00:00'),
            null,
            true,
            'Test session'
        );

        if ($finished) {
            $session->finish();
        }

        return $session;
    }

    public function test_can_create_workout_session(): void
    {
        $session = $this->createSession();

        $this->assertInstanceOf(WorkoutSessionEntity::class, $session);
        $this->assertTrue($session->isActive());
        $this->assertFalse($session->isFinished());
    }

    public function test_is_finished_returns_false_when_not_finished(): void
    {
        $session = $this->createSession();

        $this->assertFalse($session->isFinished());
    }

    public function test_is_finished_returns_true_when_finished(): void
    {
        $session = $this->createSession(finished: true);

        $this->assertTrue($session->isFinished());
    }

    public function test_can_add_sets_when_active(): void
    {
        $session = $this->createSession();

        $this->assertTrue($session->canAddSets());
    }

    public function test_cannot_add_sets_when_finished(): void
    {
        $session = $this->createSession(finished: true);

        $this->assertFalse($session->canAddSets());
    }

    public function test_finish_sets_finished_at_and_deactivates_session(): void
    {
        $session = $this->createSession();

        $session->finish();

        $this->assertFalse($session->isActive());
        $this->assertTrue($session->isFinished());
        $this->assertInstanceOf(\DateTimeImmutable::class, $session->getFinishedAt());
    }

    public function test_finish_updates_notes(): void
    {
        $session = $this->createSession();

        $session->finish('Completed all exercises');

        $this->assertEquals('Completed all exercises', $session->getNotes());
    }

    public function test_cannot_finish_twice(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('La sesión ya está finalizada');

        $session = $this->createSession(finished: true);
        $session->finish();
    }
}
