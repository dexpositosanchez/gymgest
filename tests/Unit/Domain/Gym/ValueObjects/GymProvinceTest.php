<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Gym\ValueObjects;

use App\Domain\Gym\ValueObjects\GymProvince;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class GymProvinceTest extends TestCase
{
    public function test_can_create_valid_gym_province(): void
    {
        $province = new GymProvince('Comunidad de Madrid');

        $this->assertEquals('Comunidad de Madrid', $province->getValue());
    }

    public function test_throws_exception_for_empty_province(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym province cannot be empty');

        new GymProvince('');
    }

    public function test_throws_exception_for_whitespace_only_province(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym province cannot be empty');

        new GymProvince('   ');
    }

    public function test_throws_exception_for_province_exceeding_max_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gym province cannot exceed 100 characters');

        new GymProvince(str_repeat('a', 101));
    }

    public function test_trims_whitespace_from_province(): void
    {
        $province = new GymProvince('  Comunidad de Madrid  ');

        $this->assertEquals('Comunidad de Madrid', $province->getValue());
    }
}
