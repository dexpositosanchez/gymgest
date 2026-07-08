<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Gym\ValueObjects;

use App\Domain\Gym\ValueObjects\GymCountry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GymCountryTest extends TestCase
{
    public function test_can_create_valid_gym_country(): void
    {
        $country = new GymCountry('España');

        $this->assertEquals('España', $country->getValue());
    }

    public function test_throws_exception_for_empty_country(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym country cannot be empty');

        new GymCountry('');
    }

    public function test_throws_exception_for_whitespace_only_country(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym country cannot be empty');

        new GymCountry('   ');
    }

    public function test_throws_exception_for_country_exceeding_max_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym country cannot exceed 100 characters');

        new GymCountry(str_repeat('a', 101));
    }

    public function test_trims_whitespace_from_country(): void
    {
        $country = new GymCountry('  España  ');

        $this->assertEquals('España', $country->getValue());
    }
}
