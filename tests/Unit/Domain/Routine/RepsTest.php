<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routine;

use App\Domain\Routine\ValueObjects\Reps;
use PHPUnit\Framework\TestCase;

class RepsTest extends TestCase
{
    public function test_can_create_valid_reps()
    {
        $reps = new Reps(12);
        $this->assertEquals(12, $reps->getValue());
    }

    public function test_throws_exception_for_zero_reps()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El número de repeticiones debe ser mayor a 0');
        new Reps(0);
    }

    public function test_throws_exception_for_negative_reps()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El número de repeticiones debe ser mayor a 0');
        new Reps(-5);
    }

    public function test_equals_method()
    {
        $reps1 = new Reps(10);
        $reps2 = new Reps(10);
        $reps3 = new Reps(12);

        $this->assertTrue($reps1->equals($reps2));
        $this->assertFalse($reps1->equals($reps3));
    }
}
