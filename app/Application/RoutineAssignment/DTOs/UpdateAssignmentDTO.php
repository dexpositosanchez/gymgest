<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\DTOs;

final class UpdateAssignmentDTO
{
    public $startsAt;
    public $isCurrent;
    public $notes;

    public function __construct(
        ?string $startsAt = null,
        ?bool $isCurrent = null,
        ?string $notes = null
    ) {
        $this->startsAt = $startsAt;
        $this->isCurrent = $isCurrent;
        $this->notes = $notes;
    }
}
