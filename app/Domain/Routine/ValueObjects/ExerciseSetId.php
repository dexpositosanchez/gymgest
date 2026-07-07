<?php

declare(strict_types=1);

namespace App\Domain\Routine\ValueObjects;

use Ramsey\Uuid\Uuid;

class ExerciseSetId
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
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

    public function equals(ExerciseSetId $other): bool
    {
        return $this->value === $other->value;
    }
}
