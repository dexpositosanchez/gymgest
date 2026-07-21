<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Domain\Statistics\Repositories\StatisticsRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

class GetGymActiveStudentsCountUseCase
{
    /** @var StatisticsRepositoryInterface */
    private $statisticsRepository;

    public function __construct(StatisticsRepositoryInterface $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function execute(string $gymId, string $studentId): int
    {
        if (empty($gymId)) {
            throw new InvalidArgumentException('Gym ID is required');
        }

        if (empty($studentId)) {
            throw new InvalidArgumentException('Student ID is required');
        }

        $gymIdVO = new GymId($gymId);
        $studentIdVO = new UserId($studentId);

        return $this->statisticsRepository->countGymActiveStudents($gymIdVO, $studentIdVO);
    }
}
