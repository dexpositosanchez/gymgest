<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Application\Statistics\DTOs\ActiveStudentDTO;
use App\Application\Statistics\DTOs\ActiveStudentsStatsDTO;
use App\Domain\Statistics\Repositories\StatisticsRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

class GetGymActiveStudentsUseCase
{
    /** @var StatisticsRepositoryInterface */
    private $statisticsRepository;

    public function __construct(StatisticsRepositoryInterface $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function execute(string $gymId, string $trainerId): ActiveStudentsStatsDTO
    {
        if (empty($gymId)) {
            throw new InvalidArgumentException('Gym ID is required');
        }

        if (empty($trainerId)) {
            throw new InvalidArgumentException('Trainer ID is required');
        }

        $gymIdVO = new GymId($gymId);
        $trainerIdVO = new UserId($trainerId);

        $activeStudentsData = $this->statisticsRepository->getGymActiveStudents($gymIdVO, $trainerIdVO);

        $students = array_map(function ($student) {
            return new ActiveStudentDTO(
                $student['student_id'],
                $student['student_name'],
                $student['last_workout_at']
            );
        }, $activeStudentsData);

        return new ActiveStudentsStatsDTO(
            count($students),
            $students
        );
    }
}
