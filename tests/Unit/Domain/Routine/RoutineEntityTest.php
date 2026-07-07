<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routine;

use App\Domain\Routine\Entities\RoutineEntity;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\Routine\ValueObjects\RoutineName;
use App\Domain\Routine\ValueObjects\RoutineDescription;
use App\Domain\Routine\ValueObjects\RoutineDifficulty;
use App\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class RoutineEntityTest extends TestCase
{
    private $trainerId;
    private $routineId;

    protected function setUp(): void
    {
        $this->trainerId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $this->routineId = new RoutineId('660e8400-e29b-41d4-a716-446655440000');
    }

    public function test_can_create_routine_entity()
    {
        $routine = new RoutineEntity(
            $this->routineId,
            $this->trainerId,
            new RoutineName('Push Pull Legs'),
            new RoutineDescription('Rutina dividida en 3 días'),
            RoutineDifficulty::intermediate()
        );

        $this->assertEquals('Push Pull Legs', $routine->getName()->getValue());
        $this->assertEquals('intermediate', $routine->getDifficulty()->getValue());
        $this->assertTrue($routine->belongsToTrainer($this->trainerId));
    }

    public function test_routine_is_not_assigned_by_default()
    {
        $routine = new RoutineEntity(
            $this->routineId,
            $this->trainerId,
            new RoutineName('Full Body'),
            null,
            RoutineDifficulty::beginner()
        );

        $this->assertFalse($routine->isAssigned());
    }

    public function test_can_update_routine_details()
    {
        $routine = new RoutineEntity(
            $this->routineId,
            $this->trainerId,
            new RoutineName('Original Name'),
            new RoutineDescription('Original description'),
            RoutineDifficulty::beginner()
        );

        $routine->updateDetails(
            new RoutineName('Updated Name'),
            new RoutineDescription('Updated description'),
            RoutineDifficulty::advanced()
        );

        $this->assertEquals('Updated Name', $routine->getName()->getValue());
        $this->assertEquals('Updated description', $routine->getDescription()->getValue());
        $this->assertTrue($routine->getDifficulty()->isAdvanced());
    }

    public function test_belongs_to_trainer_returns_false_for_different_trainer()
    {
        $routine = new RoutineEntity(
            $this->routineId,
            $this->trainerId,
            new RoutineName('Routine'),
            null,
            RoutineDifficulty::beginner()
        );

        $otherTrainerId = new UserId('770e8400-e29b-41d4-a716-446655440000');

        $this->assertFalse($routine->belongsToTrainer($otherTrainerId));
    }
}
