<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

class Sets
{
    /** @var int */
    private $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \DomainException('El número de series debe ser mayor a 0');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(Sets $other): bool
    {
        return $this->value === $other->value;
    }
}
