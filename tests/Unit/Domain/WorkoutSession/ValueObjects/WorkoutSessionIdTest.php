<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\WorkoutSession\ValueObjects;

use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use PHPUnit\Framework\TestCase;

class WorkoutSessionIdTest extends TestCase
{
    public function test_can_create_workout_session_id_with_valid_uuid(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $sessionId = new WorkoutSessionId($uuid);

        $this->assertEquals($uuid, $sessionId->getValue());
    }

    public function test_throws_exception_with_invalid_uuid(): void
    {
        $this->expectException(\DomainException::class);

        new WorkoutSessionId('invalid-uuid');
    }

    public function test_throws_exception_with_empty_string(): void
    {
        $this->expectException(\DomainException::class);

        new WorkoutSessionId('');
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1 = new WorkoutSessionId($uuid);
        $id2 = new WorkoutSessionId($uuid);

        $this->assertTrue($id1->equals($id2));
    }

    public function test_equals_returns_false_for_different_values(): void
    {
        $id1 = new WorkoutSessionId('550e8400-e29b-41d4-a716-446655440000');
        $id2 = new WorkoutSessionId('660e8400-e29b-41d4-a716-446655440000');

        $this->assertFalse($id1->equals($id2));
    }
}
