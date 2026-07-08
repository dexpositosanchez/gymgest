<?php

declare(strict_types=1);

namespace App\Domain\GymStudent\Services;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\GymStudent\Entities\GymStudentEntity;
use App\Domain\User\Entities\UserEntity;

class GymStudentDomainService
{
    public function canEnroll(GymEntity $gym, UserEntity $user): bool
    {
        return $user->getUserType()->getValue() === 'student';
    }

    public function getQuotaStatus(GymStudentEntity $gymStudent): string
    {
        if (!$gymStudent->isActive()) {
            return 'inactive';
        }

        if ($gymStudent->getQuotaExpiresAt()->isExpired()) {
            return 'expired';
        }

        if ($gymStudent->getQuotaExpiresAt()->isExpiringSoon()) {
            return 'expiring_soon';
        }

        return 'active';
    }
}
