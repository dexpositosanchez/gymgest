<?php

declare(strict_types=1);

namespace App\Domain\Exercise\ValueObjects;

use Ramsey\Uuid\Uuid;

class MuscleGroupId
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException("Invalid Muscle Group ID: {$value}");
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

    public function equals(MuscleGroupId $other): bool
    {
        return $this->value === $other->value;
    }
}
