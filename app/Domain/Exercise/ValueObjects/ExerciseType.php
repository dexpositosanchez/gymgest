<?php

declare(strict_types=1);

namespace App\Domain\Exercise\ValueObjects;

class ExerciseType
{
    public const DEFAULT = 'default';
    public const CUSTOM = 'custom';

    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::DEFAULT, self::CUSTOM], true)) {
            throw new \InvalidArgumentException("Invalid exercise type. Allowed values: default, custom");
        }

        $this->value = $value;
    }

    public static function default(): self
    {
        return new self(self::DEFAULT);
    }

    public static function custom(): self
    {
        return new self(self::CUSTOM);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isDefault(): bool
    {
        return $this->value === self::DEFAULT;
    }

    public function isCustom(): bool
    {
        return $this->value === self::CUSTOM;
    }

    public function equals(ExerciseType $other): bool
    {
        return $this->value === $other->value;
    }
}
