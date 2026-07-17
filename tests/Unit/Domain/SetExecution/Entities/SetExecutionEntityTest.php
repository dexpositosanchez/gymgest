<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\SetExecution\Entities;

use App\Domain\SetExecution\Entities\SetExecutionEntity;
use App\Domain\SetExecution\ValueObjects\SetExecutionId;
use App\Domain\SetExecution\ValueObjects\SetNumber;
use App\Domain\SetExecution\ValueObjects\RepsCompleted;
use App\Domain\SetExecution\ValueObjects\WeightUsed;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Routine\ValueObjects\RoutineDayExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use PHPUnit\Framework\TestCase;

class SetExecutionEntityTest extends TestCase
{
    public function test_can_create_set_execution_with_weight(): void
    {
        $setExecution = new SetExecutionEntity(
            new SetExecutionId('550e8400-e29b-41d4-a716-446655440000'),
            new WorkoutSessionId('660e8400-e29b-41d4-a716-446655440000'),
            new RoutineDayExerciseId('770e8400-e29b-41d4-a716-446655440000'),
            new ExerciseId('880e8400-e29b-41d4-a716-446655440000'),
            new SetNumber(1),
            new RepsCompleted(10),
            new WeightUsed(50.0),
            new \DateTimeImmutable()
        );

        $this->assertInstanceOf(SetExecutionEntity::class, $setExecution);
        $this->assertEquals(50.0, $setExecution->getWeightUsed()->getValue());
    }

    public function test_can_create_set_execution_without_weight(): void
    {
        $setExecution = new SetExecutionEntity(
            new SetExecutionId('550e8400-e29b-41d4-a716-446655440000'),
            new WorkoutSessionId('660e8400-e29b-41d4-a716-446655440000'),
            new RoutineDayExerciseId('770e8400-e29b-41d4-a716-446655440000'),
            new ExerciseId('880e8400-e29b-41d4-a716-446655440000'),
            new SetNumber(2),
            new RepsCompleted(12),
            new WeightUsed(null),
            new \DateTimeImmutable()
        );

        $this->assertNull($setExecution->getWeightUsed()->getValue());
    }

    public function test_has_weight_returns_true_when_weight_exists(): void
    {
        $setExecution = new SetExecutionEntity(
            new SetExecutionId('550e8400-e29b-41d4-a716-446655440000'),
            new WorkoutSessionId('660e8400-e29b-41d4-a716-446655440000'),
            new RoutineDayExerciseId('770e8400-e29b-41d4-a716-446655440000'),
            new ExerciseId('880e8400-e29b-41d4-a716-446655440000'),
            new SetNumber(1),
            new RepsCompleted(10),
            new WeightUsed(75.0),
            new \DateTimeImmutable()
        );

        $this->assertTrue($setExecution->hasWeight());
    }

    public function test_has_weight_returns_false_when_weight_is_null(): void
    {
        $setExecution = new SetExecutionEntity(
            new SetExecutionId('550e8400-e29b-41d4-a716-446655440000'),
            new WorkoutSessionId('660e8400-e29b-41d4-a716-446655440000'),
            new RoutineDayExerciseId('770e8400-e29b-41d4-a716-446655440000'),
            new ExerciseId('880e8400-e29b-41d4-a716-446655440000'),
            new SetNumber(1),
            new RepsCompleted(10),
            new WeightUsed(null),
            new \DateTimeImmutable()
        );

        $this->assertFalse($setExecution->hasWeight());
    }
}
