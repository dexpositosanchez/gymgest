<?php

declare(strict_types=1);

namespace App\Application\SetExecution\DTOs;

class SetExecutionResponseDTO
{
    /** @var string */
    public $id;

    /** @var int */
    public $setNumber;

    /** @var int */
    public $repsCompleted;

    /** @var float|null */
    public $weightUsed;

    /** @var string */
    public $completedAt;

    public function __construct(
        string $id,
        int $setNumber,
        int $repsCompleted,
        ?float $weightUsed,
        string $completedAt
    ) {
        $this->id = $id;
        $this->setNumber = $setNumber;
        $this->repsCompleted = $repsCompleted;
        $this->weightUsed = $weightUsed;
        $this->completedAt = $completedAt;
    }
}
