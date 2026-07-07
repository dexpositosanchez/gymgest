<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

use InvalidArgumentException;

class SetNumber
{
    /** @var int */
    private $value;

    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException('Set number must be at least 1');
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
