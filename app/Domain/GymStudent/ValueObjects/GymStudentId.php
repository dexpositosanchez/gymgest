<?php

declare(strict_types=1);

namespace App\Domain\GymStudent\ValueObjects;

use InvalidArgumentException;

class GymStudentId
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('GymStudent ID cannot be empty');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(GymStudentId $other): bool
    {
        return $this->value === $other->value;
    }
}
