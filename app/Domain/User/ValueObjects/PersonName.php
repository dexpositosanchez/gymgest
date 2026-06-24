<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

class PersonName
{
    private const MIN_LENGTH = 2;

    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $trimmedValue = trim($value);

        if (empty($trimmedValue)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        if (mb_strlen($trimmedValue) < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Name must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        $this->value = $trimmedValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(PersonName $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
