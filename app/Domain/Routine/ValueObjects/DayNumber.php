<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

class DayNumber
{
    /** @var int */
    private $value;

    public function __construct(int $value)
    {
        if ($value < 1 || $value > 7) {
            throw new \DomainException('El número de día debe estar entre 1 y 7');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(DayNumber $other): bool
    {
        return $this->value === $other->value;
    }
}
