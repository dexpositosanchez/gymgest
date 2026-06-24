<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use Carbon\Carbon;

class BirthDate
{
    private const MINIMUM_AGE = 16;

    /** @var Carbon */
    private $value;

    public function __construct(string $date)
    {
        $parsedDate = Carbon::parse($date);

        if ($parsedDate->isFuture()) {
            throw new \InvalidArgumentException('Birth date cannot be in the future');
        }

        $age = now()->diffInYears($parsedDate);
        if ($age < self::MINIMUM_AGE) {
            throw new \InvalidArgumentException(sprintf('User must be at least %d years old', self::MINIMUM_AGE));
        }

        $this->value = $parsedDate;
    }

    public function getValue(): Carbon
    {
        return $this->value;
    }

    public function getAge(): int
    {
        return now()->diffInYears($this->value);
    }

    public function toString(): string
    {
        return $this->value->toDateString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
