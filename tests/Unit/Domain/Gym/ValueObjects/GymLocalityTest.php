<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Gym\ValueObjects;

use App\Domain\Gym\ValueObjects\GymLocality;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GymLocalityTest extends TestCase
{
    public function test_can_create_valid_gym_locality(): void
    {
        $locality = new GymLocality('Madrid');

        $this->assertEquals('Madrid', $locality->getValue());
    }

    public function test_throws_exception_for_empty_locality(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym locality cannot be empty');

        new GymLocality('');
    }

    public function test_throws_exception_for_whitespace_only_locality(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym locality cannot be empty');

        new GymLocality('   ');
    }

    public function test_throws_exception_for_locality_exceeding_max_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym locality cannot exceed 100 characters');

        new GymLocality(str_repeat('a', 101));
    }

    public function test_trims_whitespace_from_locality(): void
    {
        $locality = new GymLocality('  Madrid  ');

        $this->assertEquals('Madrid', $locality->getValue());
    }
}
