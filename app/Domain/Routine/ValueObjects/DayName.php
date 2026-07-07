<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

class DayName
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw new \DomainException('El nombre del día no puede estar vacío');
        }

        if (strlen($trimmed) > 255) {
            throw new \DomainException('El nombre del día no puede exceder 255 caracteres');
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(DayName $other): bool
    {
        return $this->value === $other->value;
    }
}
