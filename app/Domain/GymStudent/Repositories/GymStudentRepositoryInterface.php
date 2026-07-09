<?php

declare(strict_types=1);

namespace App\Domain\GymStudent\Repositories;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Entities\GymStudentEntity;
use App\Domain\GymStudent\ValueObjects\GymStudentId;
use App\Domain\User\ValueObjects\UserId;

interface GymStudentRepositoryInterface
{
    public function save(GymStudentEntity $gymStudent): void;

    public function findById(GymStudentId $id): ?GymStudentEntity;

    public function findByGymAndStudent(GymId $gymId, UserId $studentId): ?GymStudentEntity;

    public function findByGymId(GymId $gymId): array;

    public function findByTrainerId(UserId $trainerId): array;

    public function delete(GymStudentId $id): void;

    public function countActiveByGym(GymId $gymId): int;
}
