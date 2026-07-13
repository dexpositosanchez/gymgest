<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\DTOs;

class StudentRoutineItemDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $startsAt,
        public readonly bool $isCurrent,
        public readonly string $assignedAt,
        public readonly array $routine,
        public readonly GymInfoDTO $gym,
        public readonly TrainerInfoDTO $trainer
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'starts_at' => $this->startsAt,
            'is_current' => $this->isCurrent,
            'assigned_at' => $this->assignedAt,
            'routine' => $this->routine,
            'gym' => $this->gym->toArray(),
            'trainer' => $this->trainer->toArray(),
        ];
    }
}
