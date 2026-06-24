<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

class Gender
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const OTHER = 'other';

    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $validGenders = [self::MALE, self::FEMALE, self::OTHER];

        if (!in_array($value, $validGenders, true)) {
            throw new \InvalidArgumentException('Invalid gender. Must be male, female, or other');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isMale(): bool
    {
        return $this->value === self::MALE;
    }

    public function isFemale(): bool
    {
        return $this->value === self::FEMALE;
    }

    public function isOther(): bool
    {
        return $this->value === self::OTHER;
    }

    public function equals(Gender $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function getValidGenders(): array
    {
        return [self::MALE, self::FEMALE, self::OTHER];
    }
}
