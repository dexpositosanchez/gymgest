<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routine;

use App\Domain\Routine\ValueObjects\Sets;
use PHPUnit\Framework\TestCase;

class SetsTest extends TestCase
{
    public function test_can_create_valid_sets()
    {
        $sets = new Sets(3);
        $this->assertEquals(3, $sets->getValue());
    }

    public function test_throws_exception_for_zero_sets()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El número de series debe ser mayor a 0');
        new Sets(0);
    }

    public function test_throws_exception_for_negative_sets()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El número de series debe ser mayor a 0');
        new Sets(-1);
    }

    public function test_equals_method()
    {
        $sets1 = new Sets(3);
        $sets2 = new Sets(3);
        $sets3 = new Sets(4);

        $this->assertTrue($sets1->equals($sets2));
        $this->assertFalse($sets1->equals($sets3));
    }
}
