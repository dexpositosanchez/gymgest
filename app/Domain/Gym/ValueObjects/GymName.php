<?php

declare(strict_types=1);

namespace App\Domain\Gym\ValueObjects;

use InvalidArgumentException;

final class GymName
{
    private const MAX_LENGTH = 255;

    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new InvalidArgumentException('Gym name cannot be empty');
        }

        if (strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('Gym name cannot exceed ' . self::MAX_LENGTH . ' characters');
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(GymName $other): bool
    {
        return $this->value === $other->value;
    }
}
