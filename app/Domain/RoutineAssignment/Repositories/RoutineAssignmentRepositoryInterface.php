<?php

declare(strict_types=1);

namespace App\Domain\RoutineAssignment\Repositories;

use App\Domain\RoutineAssignment\Entities\RoutineAssignmentEntity;
use App\Domain\RoutineAssignment\ValueObjects\RoutineAssignmentId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\Routine\ValueObjects\RoutineId;

interface RoutineAssignmentRepositoryInterface
{
    public function save(RoutineAssignmentEntity $assignment): void;

    public function findById(RoutineAssignmentId $id): ?RoutineAssignmentEntity;

    public function findByStudentAndGym(UserId $studentId, GymId $gymId): array;

    public function delete(RoutineAssignmentEntity $assignment): void;

    public function countByRoutineId(RoutineId $routineId): int;

    /**
     * Find non-current assignments where startsAt <= given date
     * @return RoutineAssignmentEntity[]
     */
    public function findPendingByStartsAt(string $date): array;

    /**
     * Check if a future current assignment exists for student in gym
     */
    public function hasFutureCurrentAssignment(UserId $studentId, GymId $gymId, string $afterDate): bool;

    /**
     * Find student routines with details (gym, trainer, routine)
     * Filters: gym_id, trainer_id, difficulty, is_current, from, to
     * Orders: is_current DESC, assigned_at DESC
     * Returns paginated result
     */
    public function findStudentRoutinesWithDetails(UserId $studentId, array $filters, int $page, int $perPage): array;
}
