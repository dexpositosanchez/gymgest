<?php

declare(strict_types=1);

namespace App\Application\SetExecution\DTOs;

class ExecuteSetDTO
{
    /** @var string */
    public $sessionId;

    /** @var string */
    public $exerciseId;

    /** @var int */
    public $setNumber;

    /** @var int */
    public $repsCompleted;

    /** @var float|null */
    public $weightUsed;

    public function __construct(
        string $sessionId,
        string $exerciseId,
        int $setNumber,
        int $repsCompleted,
        ?float $weightUsed
    ) {
        $this->sessionId = $sessionId;
        $this->exerciseId = $exerciseId;
        $this->setNumber = $setNumber;
        $this->repsCompleted = $repsCompleted;
        $this->weightUsed = $weightUsed;
    }
}
