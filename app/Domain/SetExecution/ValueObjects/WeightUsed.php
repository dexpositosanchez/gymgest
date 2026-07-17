<?php

declare(strict_types=1);

namespace App\Domain\SetExecution\ValueObjects;

class WeightUsed
{
    /** @var float|null */
    private $value;

    public function __construct(?float $value)
    {
        if ($value !== null && $value < 0) {
            throw new \DomainException('El peso usado debe ser mayor o igual a 0');
        }

        $this->value = $value;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function equals(WeightUsed $other): bool
    {
        return $this->value === $other->value;
    }
}
