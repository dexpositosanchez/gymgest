<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Gym\ValueObjects;

use App\Domain\Gym\ValueObjects\GymAddress;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GymAddressTest extends TestCase
{
    public function test_can_create_valid_gym_address(): void
    {
        $address = new GymAddress('Calle Gran Vía, 123');

        $this->assertEquals('Calle Gran Vía, 123', $address->getValue());
    }

    public function test_throws_exception_for_empty_address(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym address cannot be empty');

        new GymAddress('');
    }

    public function test_throws_exception_for_whitespace_only_address(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym address cannot be empty');

        new GymAddress('   ');
    }

    public function test_throws_exception_for_address_exceeding_max_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym address cannot exceed 255 characters');

        new GymAddress(str_repeat('a', 256));
    }

    public function test_trims_whitespace_from_address(): void
    {
        $address = new GymAddress('  Calle Gran Vía, 123  ');

        $this->assertEquals('Calle Gran Vía, 123', $address->getValue());
    }
}
