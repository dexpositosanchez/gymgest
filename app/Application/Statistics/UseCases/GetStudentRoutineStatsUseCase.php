<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Application\Statistics\DTOs\RoutineStatsDTO;
use App\Domain\Statistics\Repositories\StatisticsRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

class GetStudentRoutineStatsUseCase
{
    /** @var StatisticsRepositoryInterface */
    private $statisticsRepository;

    public function __construct(StatisticsRepositoryInterface $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function execute(string $studentId): array
    {
        if (empty($studentId)) {
            throw new InvalidArgumentException('Student ID is required');
        }

        $studentIdVO = new UserId($studentId);

        $statsData = $this->statisticsRepository->getStudentRoutineStats($studentIdVO);

        // Convert to DTOs
        return array_map(function ($stat) {
            return new RoutineStatsDTO(
                $stat['routine_id'],
                $stat['routine_name'],
                $stat['times_executed'],
                $stat['first_session_at'],
                $stat['last_session_at']
            );
        }, $statsData);
    }
}
