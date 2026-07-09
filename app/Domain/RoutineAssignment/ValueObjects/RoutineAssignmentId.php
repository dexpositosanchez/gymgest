<?php

declare(strict_types=1);

namespace App\Domain\RoutineAssignment\ValueObjects;

use Ramsey\Uuid\Uuid;
use InvalidArgumentException;

final class RoutineAssignmentId
{
    private $value;

    public function __construct(string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid UUID format');
        }
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(?RoutineAssignmentId $other): bool
    {
        if ($other === null) {
            return false;
        }
        return $this->value === $other->getValue();
    }
}
