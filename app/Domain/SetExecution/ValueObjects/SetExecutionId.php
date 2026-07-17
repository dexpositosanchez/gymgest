<?php

declare(strict_types=1);

namespace App\Domain\SetExecution\ValueObjects;

class SetExecutionId
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            throw new \DomainException('ID de ejecución de serie debe ser un UUID válido');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(SetExecutionId $other): bool
    {
        return $this->value === $other->value;
    }

    public static function generate(): self
    {
        return new self(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }
}
