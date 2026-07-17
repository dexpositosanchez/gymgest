<?php

declare(strict_types=1);

namespace App\Domain\ExerciseWeightHistory\ValueObjects;

class Reps
{
    /** @var int */
    private $value;

    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new \DomainException('Las repeticiones deben ser mayor o igual a 1');
        }

        if ($value > 999) {
            throw new \DomainException('Las repeticiones no pueden superar 999');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(Reps $other): bool
    {
        return $this->value === $other->value;
    }
}
