<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

class OrderIndex
{
    /** @var int */
    private $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new \DomainException('El índice de orden debe ser mayor o igual a 0');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(OrderIndex $other): bool
    {
        return $this->value === $other->value;
    }
}
