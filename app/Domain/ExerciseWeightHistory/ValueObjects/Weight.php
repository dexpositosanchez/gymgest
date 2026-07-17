<?php

declare(strict_types=1);

namespace App\Domain\ExerciseWeightHistory\ValueObjects;

class Weight
{
    /** @var float */
    private $value;

    public function __construct(float $value)
    {
        if ($value < 0) {
            throw new \DomainException('El peso debe ser mayor o igual a 0');
        }

        if ($value > 999.99) {
            throw new \DomainException('El peso no puede superar 999.99 kg');
        }

        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function equals(Weight $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }
}
