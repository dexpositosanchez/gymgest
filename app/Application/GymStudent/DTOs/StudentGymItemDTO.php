<?php

declare(strict_types=1);

namespace App\Application\GymStudent\DTOs;

class StudentGymItemDTO
{
    public function __construct(
        public readonly string $enrollment_id,
        public readonly string $enrolled_at,
        public readonly string $quota_expires_at,
        public readonly string $quota_status,
        public readonly array $gym,      // GymInfoDTO structure
        public readonly array $trainer   // TrainerInfoDTO structure
    ) {}

    public function toArray(): array
    {
        return [
            'enrollment_id' => $this->enrollment_id,
            'enrolled_at' => $this->enrolled_at,
            'quota_expires_at' => $this->quota_expires_at,
            'quota_status' => $this->quota_status,
            'gym' => $this->gym,
            'trainer' => $this->trainer,
        ];
    }
}
