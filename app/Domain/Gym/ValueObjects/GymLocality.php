<?php

declare(strict_types=1);

namespace App\Domain\Gym\ValueObjects;

use InvalidArgumentException;

class GymLocality
{
    private string $value;

    public function __construct(string $value)
    {
        $trimmedValue = trim($value);

        if (empty($trimmedValue) && $value !== 'N/A') {
            throw new InvalidArgumentException('Gym locality cannot be empty');
        }

        if (mb_strlen($trimmedValue) > 100) {
            throw new InvalidArgumentException('Gym locality cannot exceed 100 characters');
        }

        $this->value = $trimmedValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
