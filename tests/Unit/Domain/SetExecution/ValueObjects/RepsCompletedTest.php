<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\SetExecution\ValueObjects;

use App\Domain\SetExecution\ValueObjects\RepsCompleted;
use PHPUnit\Framework\TestCase;

class RepsCompletedTest extends TestCase
{
    public function test_can_create_reps_completed_with_valid_value(): void
    {
        $reps = new RepsCompleted(10);

        $this->assertEquals(10, $reps->getValue());
    }

    public function test_throws_exception_when_less_than_one(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Las repeticiones completadas deben ser mayor o igual a 1');

        new RepsCompleted(0);
    }

    public function test_throws_exception_when_negative(): void
    {
        $this->expectException(\DomainException::class);

        new RepsCompleted(-3);
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $reps1 = new RepsCompleted(12);
        $reps2 = new RepsCompleted(12);

        $this->assertTrue($reps1->equals($reps2));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $reps1 = new RepsCompleted(8);
        $reps2 = new RepsCompleted(10);

        $this->assertFalse($reps1->equals($reps2));
    }
}
