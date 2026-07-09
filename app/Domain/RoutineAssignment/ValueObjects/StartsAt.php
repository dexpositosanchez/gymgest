<?php

declare(strict_types=1);

namespace App\Domain\RoutineAssignment\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final class StartsAt
{
    private $value;

    private function __construct(DateTimeImmutable $value)
    {
        // NO validation of past/future date as per specs
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if ($dateTime === false) {
            throw new InvalidArgumentException('Invalid date format for StartsAt');
        }
        return new self($dateTime);
    }

    public function getValue(): string
    {
        return $this->value->format('Y-m-d');
    }

    public function equals(?StartsAt $other): bool
    {
        if ($other === null) {
            return false;
        }
        return $this->value == $other->value;
    }
}
