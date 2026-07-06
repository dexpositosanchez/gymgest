<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exercise\ValueObjects;

use App\Domain\Exercise\ValueObjects\ExerciseName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExerciseNameTest extends TestCase
{
    public function test_can_create_valid_exercise_name(): void
    {
        $name = new ExerciseName('Press de banca');

        $this->assertEquals('Press de banca', $name->getValue());
    }

    public function test_throws_exception_for_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise name cannot be empty');

        new ExerciseName('');
    }

    public function test_throws_exception_for_whitespace_only_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise name cannot be empty');

        new ExerciseName('   ');
    }

    public function test_throws_exception_for_name_exceeding_max_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise name cannot exceed 255 characters');

        new ExerciseName(str_repeat('a', 256));
    }

    public function test_trims_whitespace_from_name(): void
    {
        $name = new ExerciseName('  Press de banca  ');

        $this->assertEquals('Press de banca', $name->getValue());
    }
}
