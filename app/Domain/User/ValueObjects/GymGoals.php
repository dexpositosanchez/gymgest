<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

class GymGoals
{
    private const MIN_LENGTH = 5;

    /** @var string|null */
    private $value;

    public function __construct(?string $value)
    {
        if ($value === null) {
            $this->value = null;
            return;
        }

        $trimmedValue = trim($value);

        if (empty($trimmedValue)) {
            throw new \InvalidArgumentException('Gym goals cannot be empty if provided');
        }

        if (mb_strlen($trimmedValue) < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Gym goals must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        $this->value = $trimmedValue;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    public function equals(?GymGoals $other): bool
    {
        if ($other === null) {
            return false;
        }

        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }
}
