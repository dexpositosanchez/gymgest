<?php

declare(strict_types=1);

namespace App\Application\GymStudent\DTOs;

class EnrollStudentDTO
{
    public ?string $gymId;
    public string $email;
    public string $quotaExpiresAt;

    public function __construct(?string $gymId, string $email, string $quotaExpiresAt)
    {
        $this->gymId = $gymId;
        $this->email = $email;
        $this->quotaExpiresAt = $quotaExpiresAt;
    }
}
