<?php

declare(strict_types=1);

namespace App\Domain\Routine\Repositories;

use App\Domain\Routine\Entities\RoutineEntity;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\User\ValueObjects\UserId;

interface RoutineRepositoryInterface
{
    /**
     * @param RoutineId $id
     * @return RoutineEntity|null
     */
    public function findById(RoutineId $id): ?RoutineEntity;

    /**
     * @param UserId $trainerId
     * @param array $filters
     * @return RoutineEntity[]
     */
    public function findByTrainer(UserId $trainerId, array $filters = []): array;

    /**
     * @param RoutineEntity $routine
     * @return void
     */
    public function save(RoutineEntity $routine): void;

    /**
     * @param RoutineId $id
     * @return void
     */
    public function delete(RoutineId $id): void;

    /**
     * Check if routine has any assignments
     *
     * @param RoutineId $id
     * @return bool
     */
    public function hasAssignments(RoutineId $id): bool;
}
