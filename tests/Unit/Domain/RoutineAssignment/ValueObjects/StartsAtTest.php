<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\RoutineAssignment\ValueObjects;

use App\Domain\RoutineAssignment\ValueObjects\StartsAt;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StartsAtTest extends TestCase
{
    public function test_can_create_from_string(): void
    {
        $date = '2026-08-15';
        $startsAt = StartsAt::fromString($date);

        $this->assertEquals($date, $startsAt->getValue());
    }

    public function test_get_value_returns_date_string(): void
    {
        $date = '2026-09-20';
        $startsAt = StartsAt::fromString($date);

        $value = $startsAt->getValue();

        $this->assertIsString($value);
        $this->assertEquals($date, $value);
    }

    public function test_equals_returns_true_for_same_date(): void
    {
        $date = '2026-10-10';
        $startsAt1 = StartsAt::fromString($date);
        $startsAt2 = StartsAt::fromString($date);

        $this->assertTrue($startsAt1->equals($startsAt2));
    }

    public function test_allows_past_dates(): void
    {
        $pastDate = date('Y-m-d', strtotime('-30 days'));
        $startsAt = StartsAt::fromString($pastDate);

        $this->assertEquals($pastDate, $startsAt->getValue());
    }

    public function test_allows_future_dates(): void
    {
        $futureDate = date('Y-m-d', strtotime('+60 days'));
        $startsAt = StartsAt::fromString($futureDate);

        $this->assertEquals($futureDate, $startsAt->getValue());
    }
}
