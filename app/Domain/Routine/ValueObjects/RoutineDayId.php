<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

use Ramsey\Uuid\Uuid;

class RoutineDayId
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new \DomainException('ID de día de rutina inválido');
        }

        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(RoutineDayId $other): bool
    {
        return $this->value === $other->value;
    }
}
