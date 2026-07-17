<?php

declare(strict_types=1);

namespace App\Domain\WorkoutSession\Repositories;

use App\Domain\WorkoutSession\Entities\WorkoutSessionEntity;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\User\ValueObjects\UserId;

interface WorkoutSessionRepositoryInterface
{
    public function save(WorkoutSessionEntity $session): void;

    public function findById(WorkoutSessionId $id): ?WorkoutSessionEntity;

    public function findActiveByStudent(UserId $studentId): ?WorkoutSessionEntity;

    /**
     * @param UserId $studentId
     * @param int $page
     * @param int $perPage
     * @return array{data: WorkoutSessionEntity[], total: int, current_page: int, per_page: int, last_page: int}
     */
    public function findHistoryByStudentId(UserId $studentId, int $page = 1, int $perPage = 15): array;

    public function hasActiveSession(UserId $studentId): bool;
}
