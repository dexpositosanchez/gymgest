<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Routine;

use App\Domain\Routine\ValueObjects\RoutineDifficulty;
use PHPUnit\Framework\TestCase;

class RoutineDifficultyTest extends TestCase
{
    public function test_can_create_beginner_difficulty()
    {
        $difficulty = RoutineDifficulty::beginner();
        $this->assertEquals('beginner', $difficulty->getValue());
        $this->assertTrue($difficulty->isBeginner());
        $this->assertFalse($difficulty->isIntermediate());
        $this->assertFalse($difficulty->isAdvanced());
    }

    public function test_can_create_intermediate_difficulty()
    {
        $difficulty = RoutineDifficulty::intermediate();
        $this->assertEquals('intermediate', $difficulty->getValue());
        $this->assertFalse($difficulty->isBeginner());
        $this->assertTrue($difficulty->isIntermediate());
        $this->assertFalse($difficulty->isAdvanced());
    }

    public function test_can_create_advanced_difficulty()
    {
        $difficulty = RoutineDifficulty::advanced();
        $this->assertEquals('advanced', $difficulty->getValue());
        $this->assertFalse($difficulty->isBeginner());
        $this->assertFalse($difficulty->isIntermediate());
        $this->assertTrue($difficulty->isAdvanced());
    }

    public function test_can_create_from_string()
    {
        $difficulty = RoutineDifficulty::fromString('beginner');
        $this->assertTrue($difficulty->isBeginner());
    }

    public function test_throws_exception_for_invalid_difficulty()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Dificultad inválida');
        RoutineDifficulty::fromString('expert');
    }

    public function test_equals_method()
    {
        $difficulty1 = RoutineDifficulty::beginner();
        $difficulty2 = RoutineDifficulty::beginner();
        $difficulty3 = RoutineDifficulty::advanced();

        $this->assertTrue($difficulty1->equals($difficulty2));
        $this->assertFalse($difficulty1->equals($difficulty3));
    }
}
