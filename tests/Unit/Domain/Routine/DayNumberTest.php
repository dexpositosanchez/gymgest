<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routine;

use App\Domain\Routine\ValueObjects\DayNumber;
use PHPUnit\Framework\TestCase;

class DayNumberTest extends TestCase
{
    public function test_can_create_valid_day_number()
    {
        $dayNumber = new DayNumber(1);
        $this->assertEquals(1, $dayNumber->getValue());
    }

    public function test_accepts_day_number_from_1_to_7()
    {
        foreach (range(1, 7) as $day) {
            $dayNumber = new DayNumber($day);
            $this->assertEquals($day, $dayNumber->getValue());
        }
    }

    public function test_throws_exception_for_day_number_less_than_1()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El número de día debe estar entre 1 y 7');
        new DayNumber(0);
    }

    public function test_throws_exception_for_day_number_greater_than_7()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('El número de día debe estar entre 1 y 7');
        new DayNumber(8);
    }

    public function test_equals_method()
    {
        $day1 = new DayNumber(1);
        $day2 = new DayNumber(1);
        $day3 = new DayNumber(2);

        $this->assertTrue($day1->equals($day2));
        $this->assertFalse($day1->equals($day3));
    }
}
