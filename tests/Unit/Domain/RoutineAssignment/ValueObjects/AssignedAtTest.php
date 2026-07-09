<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\RoutineAssignment\ValueObjects;

use App\Domain\RoutineAssignment\ValueObjects\AssignedAt;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AssignedAtTest extends TestCase
{
    public function test_can_create_assigned_at_now(): void
    {
        $before = date('Y-m-d H:i:s');
        $assignedAt = AssignedAt::now();
        $after = date('Y-m-d H:i:s');

        $this->assertGreaterThanOrEqual($before, $assignedAt->getValue());
        $this->assertLessThanOrEqual($after, $assignedAt->getValue());
    }

    public function test_can_create_from_string(): void
    {
        $dateTime = '2026-07-08 10:30:00';
        $assignedAt = AssignedAt::fromString($dateTime);

        $this->assertEquals($dateTime, $assignedAt->getValue());
    }

    public function test_get_value_returns_datetime_string(): void
    {
        $dateTime = '2026-06-01 15:45:30';
        $assignedAt = AssignedAt::fromString($dateTime);

        $value = $assignedAt->getValue();

        $this->assertIsString($value);
        $this->assertEquals($dateTime, $value);
    }

    public function test_equals_returns_true_for_same_datetime(): void
    {
        $dateTime = '2026-05-15 12:00:00';
        $assignedAt1 = AssignedAt::fromString($dateTime);
        $assignedAt2 = AssignedAt::fromString($dateTime);

        $this->assertTrue($assignedAt1->equals($assignedAt2));
    }
}
