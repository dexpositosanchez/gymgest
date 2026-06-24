<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

class UserType
{
    public const TRAINER = 'trainer';
    public const STUDENT = 'student';

    /** @var string */
    private $value;

    public function __construct(?string $value)
    {
        if ($value === null) {
            throw new \InvalidArgumentException('User type cannot be null');
        }

        $validTypes = [self::TRAINER, self::STUDENT];
        if (!in_array($value, $validTypes, true)) {
            throw new \InvalidArgumentException('Invalid user type. Must be trainer or student');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isTrainer(): bool
    {
        return $this->value === self::TRAINER;
    }

    public function isStudent(): bool
    {
        return $this->value === self::STUDENT;
    }

    public function equals(UserType $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function getValidTypes(): array
    {
        return [self::TRAINER, self::STUDENT];
    }
}