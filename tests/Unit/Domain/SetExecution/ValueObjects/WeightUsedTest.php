<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\SetExecution\ValueObjects;

use App\Domain\SetExecution\ValueObjects\WeightUsed;
use PHPUnit\Framework\TestCase;

class WeightUsedTest extends TestCase
{
    public function test_can_create_weight_used_with_valid_value(): void
    {
        $weight = new WeightUsed(50.5);

        $this->assertEquals(50.5, $weight->getValue());
    }

    public function test_can_create_weight_used_with_null(): void
    {
        $weight = new WeightUsed(null);

        $this->assertNull($weight->getValue());
    }

    public function test_throws_exception_when_negative(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El peso usado debe ser mayor o igual a 0');

        new WeightUsed(-10.0);
    }

    public function test_allows_zero_weight(): void
    {
        $weight = new WeightUsed(0.0);

        $this->assertEquals(0.0, $weight->getValue());
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $weight1 = new WeightUsed(75.5);
        $weight2 = new WeightUsed(75.5);

        $this->assertTrue($weight1->equals($weight2));
    }

    public function test_equals_returns_true_for_both_null(): void
    {
        $weight1 = new WeightUsed(null);
        $weight2 = new WeightUsed(null);

        $this->assertTrue($weight1->equals($weight2));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $weight1 = new WeightUsed(50.0);
        $weight2 = new WeightUsed(60.0);

        $this->assertFalse($weight1->equals($weight2));
    }

    public function test_equals_returns_false_when_one_is_null(): void
    {
        $weight1 = new WeightUsed(50.0);
        $weight2 = new WeightUsed(null);

        $this->assertFalse($weight1->equals($weight2));
    }
}
