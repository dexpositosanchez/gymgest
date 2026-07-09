<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\RoutineAssignment\ValueObjects;

use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RoutineAssignmentIdTest extends TestCase
{
    public function test_can_create_from_valid_uuid(): void
    {
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $id = new RoutineAssignmentId($uuid);

        $this->assertEquals($uuid, $id->getValue());
    }

    public function test_can_create_from_string_factory(): void
    {
        $uuid = '223e4567-e89b-12d3-a456-426614174000';
        $id = RoutineAssignmentId::fromString($uuid);

        $this->assertEquals($uuid, $id->getValue());
    }

    public function test_get_value_returns_uuid_string(): void
    {
        $uuid = '323e4567-e89b-12d3-a456-426614174000';
        $id = new RoutineAssignmentId($uuid);

        $value = $id->getValue();

        $this->assertIsString($value);
        $this->assertEquals($uuid, $value);
    }

    public function test_equals_returns_true_for_same_id(): void
    {
        $uuid = '423e4567-e89b-12d3-a456-426614174000';
        $id1 = new RoutineAssignmentId($uuid);
        $id2 = new RoutineAssignmentId($uuid);

        $this->assertTrue($id1->equals($id2));
    }

    public function test_throws_exception_for_invalid_uuid_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format');

        new RoutineAssignmentId('invalid-uuid');
    }
}
