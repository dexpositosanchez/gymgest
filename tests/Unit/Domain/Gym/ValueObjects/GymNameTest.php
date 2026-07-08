<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Gym\ValueObjects;

use App\Domain\Gym\ValueObjects\GymName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GymNameTest extends TestCase
{
    public function test_can_create_valid_gym_name(): void
    {
        $name = new GymName('FitZone Madrid Centro');

        $this->assertEquals('FitZone Madrid Centro', $name->getValue());
    }

    public function test_throws_exception_for_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym name cannot be empty');

        new GymName('');
    }

    public function test_throws_exception_for_whitespace_only_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym name cannot be empty');

        new GymName('   ');
    }

    public function test_throws_exception_for_name_exceeding_max_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym name cannot exceed 255 characters');

        new GymName(str_repeat('a', 256));
    }

    public function test_trims_whitespace_from_name(): void
    {
        $name = new GymName('  FitZone Madrid Centro  ');

        $this->assertEquals('FitZone Madrid Centro', $name->getValue());
    }
}
