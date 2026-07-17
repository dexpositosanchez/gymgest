<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ExerciseWeightHistory\Entities;

use App\Domain\ExerciseWeightHistory\Entities\ExerciseWeightHistoryEntity;
use App\Domain\ExerciseWeightHistory\ValueObjects\ExerciseWeightHistoryId;
use App\Domain\ExerciseWeightHistory\ValueObjects\Weight;
use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use PHPUnit\Framework\TestCase;

class ExerciseWeightHistoryEntityTest extends TestCase
{
    public function test_can_create_exercise_weight_history(): void
    {
        $history = new ExerciseWeightHistoryEntity(
            new ExerciseWeightHistoryId('550e8400-e29b-41d4-a716-446655440000'),
            new UserId('660e8400-e29b-41d4-a716-446655440000'),
            new ExerciseId('770e8400-e29b-41d4-a716-446655440000'),
            new Reps(10),
            new Weight(80.0),
            new \DateTimeImmutable('2026-07-15 10:00:00')
        );

        $this->assertInstanceOf(ExerciseWeightHistoryEntity::class, $history);
        $this->assertEquals(80.0, $history->getWeight()->getValue());
        $this->assertEquals(10, $history->getReps()->getValue());
    }

    public function test_can_update_weight(): void
    {
        $history = new ExerciseWeightHistoryEntity(
            new ExerciseWeightHistoryId('550e8400-e29b-41d4-a716-446655440000'),
            new UserId('660e8400-e29b-41d4-a716-446655440000'),
            new ExerciseId('770e8400-e29b-41d4-a716-446655440000'),
            new Reps(10),
            new Weight(80.0),
            new \DateTimeImmutable('2026-07-15 10:00:00')
        );

        $newWeight = new Weight(85.0);
        $history->updateWeight($newWeight);

        $this->assertEquals(85.0, $history->getWeight()->getValue());
    }

    public function test_update_weight_updates_last_used_at(): void
    {
        $initialTime = new \DateTimeImmutable('2026-07-15 10:00:00');
        $history = new ExerciseWeightHistoryEntity(
            new ExerciseWeightHistoryId('550e8400-e29b-41d4-a716-446655440000'),
            new UserId('660e8400-e29b-41d4-a716-446655440000'),
            new ExerciseId('770e8400-e29b-41d4-a716-446655440000'),
            new Reps(10),
            new Weight(80.0),
            $initialTime
        );

        sleep(1);
        $newWeight = new Weight(85.0);
        $history->updateWeight($newWeight);

        $this->assertGreaterThan($initialTime->getTimestamp(), $history->getLastUsedAt()->getTimestamp());
    }
}
