<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\SetExecution\ValueObjects;

use App\Domain\SetExecution\ValueObjects\SetNumber;
use PHPUnit\Framework\TestCase;

class SetNumberTest extends TestCase
{
    public function test_can_create_set_number_with_valid_value(): void
    {
        $setNumber = new SetNumber(1);

        $this->assertEquals(1, $setNumber->getValue());
    }

    public function test_throws_exception_when_less_than_one(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El número de serie debe ser mayor o igual a 1');

        new SetNumber(0);
    }

    public function test_throws_exception_when_negative(): void
    {
        $this->expectException(\DomainException::class);

        new SetNumber(-5);
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $set1 = new SetNumber(3);
        $set2 = new SetNumber(3);

        $this->assertTrue($set1->equals($set2));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $set1 = new SetNumber(1);
        $set2 = new SetNumber(2);

        $this->assertFalse($set1->equals($set2));
    }
}
