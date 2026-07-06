<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exercise\ValueObjects;

use App\Domain\Exercise\ValueObjects\ExerciseDescription;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExerciseDescriptionTest extends TestCase
{
    public function test_can_create_valid_exercise_description(): void
    {
        $description = new ExerciseDescription('This is a valid description for an exercise');

        $this->assertEquals('This is a valid description for an exercise', $description->getValue());
    }

    public function test_throws_exception_for_empty_description(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise description cannot be empty');

        new ExerciseDescription('');
    }

    public function test_throws_exception_for_whitespace_only_description(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise description cannot be empty');

        new ExerciseDescription('   ');
    }

    public function test_throws_exception_for_description_less_than_min_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise description must be at least 10 characters');

        new ExerciseDescription('Short');
    }

    public function test_trims_whitespace_from_description(): void
    {
        $description = new ExerciseDescription('  This is a valid description  ');

        $this->assertEquals('This is a valid description', $description->getValue());
    }

    public function test_accepts_description_with_exactly_min_length(): void
    {
        $description = new ExerciseDescription('1234567890');

        $this->assertEquals('1234567890', $description->getValue());
    }
}
