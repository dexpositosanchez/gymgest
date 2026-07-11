<?php

declare(strict_types=1);

namespace App\Domain\Gym\ValueObjects;

use InvalidArgumentException;

class GymProvince
{
    private string $value;

    public function __construct(string $value)
    {
        $trimmedValue = trim($value);

        if (empty($trimmedValue) && $value !== 'N/A') {
            throw new InvalidArgumentException('Gym province cannot be empty');
        }

        if (mb_strlen($trimmedValue) > 100) {
            throw new InvalidArgumentException('Gym province cannot exceed 100 characters');
        }

        $this->value = $trimmedValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
