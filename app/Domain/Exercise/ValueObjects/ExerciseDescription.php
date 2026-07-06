<?php

declare(strict_types=1);

namespace App\Domain\Exercise\ValueObjects;

class ExerciseDescription
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Exercise description cannot be empty');
        }

        if (strlen($trimmed) < 10) {
            throw new \InvalidArgumentException('Exercise description must be at least 10 characters');
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
