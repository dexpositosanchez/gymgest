<?php

declare(strict_types=1);

namespace App\Application\RoutineAssignment\DTOs;

class StudentRoutinesResponseDTO
{
    public function __construct(
        public readonly array $data,
        public readonly array $meta
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(fn($item) => $item->toArray(), $this->data),
            'meta' => $this->meta,
        ];
    }
}
