<?php

declare(strict_types=1);

namespace App\Domain\SetExecution\ValueObjects;

class RepsCompleted
{
    /** @var int */
    private $value;

    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new \DomainException('Las repeticiones completadas deben ser mayor o igual a 1');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(RepsCompleted $other): bool
    {
        return $this->value === $other->value;
    }
}
