<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\DTOs;

class TrainerInfoDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
