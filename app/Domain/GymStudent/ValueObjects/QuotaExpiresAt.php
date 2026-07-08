<?php

declare(strict_types=1);

namespace App\Domain\GymStudent\ValueObjects;

use DateTime;
use InvalidArgumentException;

class QuotaExpiresAt
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Quota expiration date cannot be empty');
        }

        if (!$this->isValidDate($value)) {
            throw new InvalidArgumentException('Invalid date format');
        }

        $this->value = $value;
    }

    public static function createForEnrollment(string $value): self
    {
        $instance = new self($value);

        $date = new DateTime($value);
        $today = new DateTime('today');

        if ($date <= $today) {
            throw new InvalidArgumentException('Quota expiration date must be in the future');
        }

        return $instance;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isExpired(): bool
    {
        $date = new DateTime($this->value);
        $today = new DateTime('today');

        return $date <= $today;
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        $date = new DateTime($this->value);
        $threshold = new DateTime("+{$days} days");

        return $date <= $threshold;
    }

    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
