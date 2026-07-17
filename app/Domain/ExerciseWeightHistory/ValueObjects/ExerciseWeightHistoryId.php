<?php

declare(strict_types=1);

namespace App\Domain\ExerciseWeightHistory\ValueObjects;

use Ramsey\Uuid\Uuid;

class ExerciseWeightHistoryId
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new \DomainException('El ID del historial de peso debe ser un UUID válido');
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

    public function equals(ExerciseWeightHistoryId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
