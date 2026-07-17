<?php

declare(strict_types=1);

namespace App\Application\WorkoutSession\UseCases;

use App\Domain\WorkoutSession\Repositories\WorkoutSessionRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class GetWorkoutHistoryUseCase
{
    /** @var WorkoutSessionRepositoryInterface */
    private $sessionRepository;

    public function __construct(WorkoutSessionRepositoryInterface $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @param string $studentId
     * @param int $page
     * @param int $perPage
     * @return array{data: \App\Domain\WorkoutSession\Entities\WorkoutSessionEntity[], total: int, current_page: int, per_page: int, last_page: int}
     */
    public function execute(string $studentId, int $page = 1, int $perPage = 15): array
    {
        return $this->sessionRepository->findHistoryByStudentId(
            new UserId($studentId),
            $page,
            $perPage
        );
    }
}
