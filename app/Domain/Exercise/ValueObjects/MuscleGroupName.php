<?php

declare(strict_types=1);

namespace App\Domain\Exercise\ValueObjects;

class MuscleGroupName
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Muscle group name cannot be empty');
        }

        if (strlen($trimmed) > 100) {
            throw new \InvalidArgumentException('Muscle group name cannot exceed 100 characters');
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
