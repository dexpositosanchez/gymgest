<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ExerciseWeightHistory\ValueObjects;

use App\Domain\ExerciseWeightHistory\ValueObjects\Weight;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    public function test_can_create_weight_with_valid_value(): void
    {
        $weight = new Weight(100.5);

        $this->assertEquals(100.5, $weight->getValue());
    }

    public function test_throws_exception_when_negative(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El peso debe ser mayor o igual a 0');

        new Weight(-5.0);
    }

    public function test_allows_zero_weight(): void
    {
        $weight = new Weight(0.0);

        $this->assertEquals(0.0, $weight->getValue());
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $weight1 = new Weight(80.0);
        $weight2 = new Weight(80.0);

        $this->assertTrue($weight1->equals($weight2));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $weight1 = new Weight(70.0);
        $weight2 = new Weight(80.0);

        $this->assertFalse($weight1->equals($weight2));
    }
}
