<?php

declare(strict_types=1);

namespace App\Domain\RoutineAssignment\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final class AssignedAt
{
    private $value;

    private function __construct(DateTimeImmutable $value)
    {
        $this->value = $value;
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }

    public static function fromString(string $value): self
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        if ($dateTime === false) {
            throw new InvalidArgumentException('Invalid datetime format for AssignedAt');
        }
        return new self($dateTime);
    }

    public function getValue(): string
    {
        return $this->value->format('Y-m-d H:i:s');
    }

    public function equals(?AssignedAt $other): bool
    {
        if ($other === null) {
            return false;
        }
        return $this->value == $other->value;
    }
}
