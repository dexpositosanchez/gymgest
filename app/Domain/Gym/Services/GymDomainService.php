<?php

declare(strict_types=1);

namespace App\Domain\Gym\Services;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\User\ValueObjects\UserId;

final class GymDomainService
{
    public function canTrainerModify(GymEntity $gym, UserId $trainerId): bool
    {
        return $gym->belongsToTrainer($trainerId);
    }

    public function isAssigned(GymEntity $gym): bool
    {
        return $gym->isAssigned();
    }
}
