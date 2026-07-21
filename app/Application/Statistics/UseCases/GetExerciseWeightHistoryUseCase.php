<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Application\Statistics\DTOs\ExerciseWeightHistoryEntryDTO;
use App\Domain\Statistics\Repositories\StatisticsRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use InvalidArgumentException;

class GetExerciseWeightHistoryUseCase
{
    /** @var StatisticsRepositoryInterface */
    private $statisticsRepository;

    public function __construct(StatisticsRepositoryInterface $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function execute(string $studentId, string $exerciseId, int $reps): array
    {
        if (empty($studentId)) {
            throw new InvalidArgumentException('Student ID is required');
        }

        if (empty($exerciseId)) {
            throw new InvalidArgumentException('Exercise ID is required');
        }

        if ($reps <= 0) {
            throw new InvalidArgumentException('Reps must be greater than 0');
        }

        $studentIdVO = new UserId($studentId);
        $exerciseIdVO = new ExerciseId($exerciseId);

        $historyData = $this->statisticsRepository->getExerciseWeightHistory($studentIdVO, $exerciseIdVO, $reps);

        // Convert to DTOs
        return array_map(function ($entry) {
            return new ExerciseWeightHistoryEntryDTO(
                $entry['date'],
                $entry['weight'],
                $entry['reps']
            );
        }, $historyData);
    }
}
