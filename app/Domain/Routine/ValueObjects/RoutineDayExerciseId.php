<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

use Ramsey\Uuid\Uuid;

class RoutineDayExerciseId
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new \DomainException('ID de ejercicio de día de rutina inválido');
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

    public function equals(RoutineDayExerciseId $other): bool
    {
        return $this->value === $other->value;
    }
}
