<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

class RoutineDifficulty
{
    private const BEGINNER = 'beginner';
    private const INTERMEDIATE = 'intermediate';
    private const ADVANCED = 'advanced';

    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function beginner(): self
    {
        return new self(self::BEGINNER);
    }

    public static function intermediate(): self
    {
        return new self(self::INTERMEDIATE);
    }

    public static function advanced(): self
    {
        return new self(self::ADVANCED);
    }

    public static function fromString(string $value): self
    {
        $normalized = strtolower(trim($value));

        if (!in_array($normalized, [self::BEGINNER, self::INTERMEDIATE, self::ADVANCED], true)) {
            throw new \DomainException('Dificultad inválida');
        }

        return new self($normalized);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isBeginner(): bool
    {
        return $this->value === self::BEGINNER;
    }

    public function isIntermediate(): bool
    {
        return $this->value === self::INTERMEDIATE;
    }

    public function isAdvanced(): bool
    {
        return $this->value === self::ADVANCED;
    }

    public function equals(RoutineDifficulty $other): bool
    {
        return $this->value === $other->value;
    }
}
