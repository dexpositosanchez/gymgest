<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\DTOs;

class GymInfoDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly bool $isPersonalTraining
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_personal_training' => $this->isPersonalTraining,
        ];
    }
}
