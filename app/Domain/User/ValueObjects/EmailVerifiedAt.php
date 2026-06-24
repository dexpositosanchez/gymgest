<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

class EmailVerifiedAt
{
    /** @var \DateTimeInterface|null */
    private $value;

    public function __construct(?\DateTimeInterface $value = null)
    {
        $this->value = $value;
    }

    public static function fromString(?string $dateString): self
    {
        if ($dateString === null) {
            return new self(null);
        }

        return new self(new \DateTime($dateString));
    }

    public static function now(): self
    {
        return new self(new \DateTime());
    }

    public function isVerified(): bool
    {
        return $this->value !== null;
    }

    public function getValue(): ?\DateTimeInterface
    {
        return $this->value;
    }

    public function toString(): ?string
    {
        if ($this->value === null) {
            return null;
        }

        return $this->value->format('Y-m-d H:i:s');
    }

    public function equals(?EmailVerifiedAt $other): bool
    {
        if ($other === null) {
            return false;
        }

        if ($this->value === null && $other->value === null) {
            return true;
        }

        if ($this->value === null || $other->value === null) {
            return false;
        }

        return $this->value->getTimestamp() === $other->value->getTimestamp();
    }
}
