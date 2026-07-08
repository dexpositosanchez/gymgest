<?php

declare(strict_types=1);

namespace App\Application\GymStudent\DTOs;

class GymStudentResponseDTO
{
    public string $id;
    public string $gymId;
    public string $studentId;
    public string $studentName;
    public string $studentEmail;
    public string $quotaExpiresAt;
    public bool $isActive;
    public string $quotaStatus;

    public function __construct(
        string $id,
        string $gymId,
        string $studentId,
        string $studentName,
        string $studentEmail,
        string $quotaExpiresAt,
        bool $isActive,
        string $quotaStatus
    ) {
        $this->id = $id;
        $this->gymId = $gymId;
        $this->studentId = $studentId;
        $this->studentName = $studentName;
        $this->studentEmail = $studentEmail;
        $this->quotaExpiresAt = $quotaExpiresAt;
        $this->isActive = $isActive;
        $this->quotaStatus = $quotaStatus;
    }
}
