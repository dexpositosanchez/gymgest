<?php

declare(strict_types=1);

namespace App\Domain\Gym\Services;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\Gym\ValueObjects\GymAddress;
use App\Domain\Gym\ValueObjects\GymLocality;
use App\Domain\Gym\ValueObjects\GymProvince;
use App\Domain\Gym\ValueObjects\GymCountry;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Gym\ValueObjects\GymName;
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

    public function createPersonalTrainingGym(GymId $id, UserId $trainerId): GymEntity
    {
        return new GymEntity(
            $id,
            $trainerId,
            new GymName('Entrenamiento Personal'),
            new GymAddress('N/A'),
            new GymLocality('N/A'),
            new GymProvince('N/A'),
            new GymCountry('N/A'),
            true,
            true // is_personal_training
        );
    }
}
