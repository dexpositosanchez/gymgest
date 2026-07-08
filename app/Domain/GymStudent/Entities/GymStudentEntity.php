<?php

declare(strict_types=1);

namespace App\Domain\GymStudent\Entities;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\ValueObjects\GymStudentId;
use App\Domain\GymStudent\ValueObjects\QuotaExpiresAt;
use App\Domain\User\ValueObjects\UserId;

class GymStudentEntity
{
    private GymStudentId $id;
    private GymId $gymId;
    private UserId $studentId;
    private QuotaExpiresAt $quotaExpiresAt;
    private bool $isActive;

    public function __construct(
        GymStudentId $id,
        GymId $gymId,
        UserId $studentId,
        QuotaExpiresAt $quotaExpiresAt,
        bool $isActive = true
    ) {
        $this->id = $id;
        $this->gymId = $gymId;
        $this->studentId = $studentId;
        $this->quotaExpiresAt = $quotaExpiresAt;
        $this->isActive = $isActive;
    }

    public function getId(): GymStudentId
    {
        return $this->id;
    }

    public function getGymId(): GymId
    {
        return $this->gymId;
    }

    public function getStudentId(): UserId
    {
        return $this->studentId;
    }

    public function getQuotaExpiresAt(): QuotaExpiresAt
    {
        return $this->quotaExpiresAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function updateQuotaExpiresAt(QuotaExpiresAt $quotaExpiresAt): void
    {
        $this->quotaExpiresAt = $quotaExpiresAt;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function reactivate(QuotaExpiresAt $quotaExpiresAt): void
    {
        $this->isActive = true;
        $this->quotaExpiresAt = $quotaExpiresAt;
    }

    public function belongsToGym(GymId $gymId): bool
    {
        return $this->gymId->getValue() === $gymId->getValue();
    }

    public function isStudent(UserId $userId): bool
    {
        return $this->studentId->getValue() === $userId->getValue();
    }
}
