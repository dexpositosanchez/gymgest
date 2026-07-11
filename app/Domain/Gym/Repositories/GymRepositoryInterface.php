<?php

declare(strict_types=1);

namespace App\Domain\Gym\Repositories;

use App\Domain\Gym\Entities\GymEntity;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;

interface GymRepositoryInterface
{
    public function save(GymEntity $gym): void;

    public function findById(GymId $id): ?GymEntity;

    public function findByTrainerId(UserId $trainerId, bool $includeInactive = false): array;

    public function findPersonalTrainingGymByTrainer(UserId $trainerId): ?GymEntity;

    public function delete(GymId $id): void;
}
