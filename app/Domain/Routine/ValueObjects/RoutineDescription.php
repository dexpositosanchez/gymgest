<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

class RoutineDescription
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $this->value = trim($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(RoutineDescription $other): bool
    {
        return $this->value === $other->value;
    }
}
