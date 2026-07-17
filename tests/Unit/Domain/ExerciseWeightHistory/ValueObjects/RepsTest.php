<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ExerciseWeightHistory\ValueObjects;

use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;
use PHPUnit\Framework\TestCase;

class RepsTest extends TestCase
{
    public function test_can_create_reps_with_valid_value(): void
    {
        $reps = new Reps(12);

        $this->assertEquals(12, $reps->getValue());
    }

    public function test_throws_exception_when_less_than_one(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Las repeticiones deben ser mayor o igual a 1');

        new Reps(0);
    }

    public function test_throws_exception_when_negative(): void
    {
        $this->expectException(\DomainException::class);

        new Reps(-10);
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $reps1 = new Reps(8);
        $reps2 = new Reps(8);

        $this->assertTrue($reps1->equals($reps2));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $reps1 = new Reps(10);
        $reps2 = new Reps(12);

        $this->assertFalse($reps1->equals($reps2));
    }
}
