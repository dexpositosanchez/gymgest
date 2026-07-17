<?php

declare(strict_types=1);

namespace App\Domain\SetExecution\ValueObjects;

class SetNumber
{
    /** @var int */
    private $value;

    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new \DomainException('El número de serie debe ser mayor o igual a 1');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(SetNumber $other): bool
    {
        return $this->value === $other->value;
    }
}
