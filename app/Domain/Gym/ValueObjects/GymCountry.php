<?php

declare(strict_types=1);

namespace App\Domain\Gym\ValueObjects;

use InvalidArgumentException;

final class GymCountry
{
    private const MAX_LENGTH = 100;

    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed) && $value !== 'N/A') {
            throw new InvalidArgumentException('Gym country cannot be empty');
        }

        if (strlen($trimmed) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('Gym country cannot exceed ' . self::MAX_LENGTH . ' characters');
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(GymCountry $other): bool
    {
        return $this->value === $other->value;
    }
}
