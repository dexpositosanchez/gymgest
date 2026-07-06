<?php

declare(strict_types=1);

namespace App\Domain\Exercise\ValueObjects;

class ExerciseName
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Exercise name cannot be empty');
        }

        if (strlen($trimmed) > 255) {
            throw new \InvalidArgumentException('Exercise name cannot exceed 255 characters');
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
