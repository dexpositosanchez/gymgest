<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exercise\ValueObjects;

use App\Domain\Exercise\ValueObjects\ExerciseType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExerciseTypeTest extends TestCase
{
    public function test_can_create_default_type(): void
    {
        $type = ExerciseType::default();

        $this->assertTrue($type->isDefault());
        $this->assertFalse($type->isCustom());
        $this->assertEquals(ExerciseType::DEFAULT, $type->getValue());
    }

    public function test_can_create_custom_type(): void
    {
        $type = ExerciseType::custom();

        $this->assertTrue($type->isCustom());
        $this->assertFalse($type->isDefault());
        $this->assertEquals(ExerciseType::CUSTOM, $type->getValue());
    }

    public function test_throws_exception_for_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid exercise type. Allowed values: default, custom');

        new ExerciseType('invalid');
    }

    public function test_equals_method_works_correctly(): void
    {
        $type1 = ExerciseType::default();
        $type2 = ExerciseType::default();
        $type3 = ExerciseType::custom();

        $this->assertTrue($type1->equals($type2));
        $this->assertFalse($type1->equals($type3));
    }
}
