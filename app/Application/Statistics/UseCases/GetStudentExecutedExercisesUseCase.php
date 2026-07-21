<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Application\Statistics\DTOs\ExecutedExerciseDTO;
use App\Domain\Statistics\Repositories\StatisticsRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;
use InvalidArgumentException;

class GetStudentExecutedExercisesUseCase
{
    /** @var StatisticsRepositoryInterface */
    private $statisticsRepository;

    public function __construct(StatisticsRepositoryInterface $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    public function execute(string $studentId): array
    {
        // Verificar: studentId debe ser un UUID válido
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $studentId)) {
            throw new InvalidArgumentException('Invalid student ID format');
        }

        $studentIdVO = new UserId($studentId);

        $exercisesData = $this->statisticsRepository->getStudentExecutedExercises($studentIdVO);

        // Convertir a DTOs
        $exercises = [];
        foreach ($exercisesData as $exerciseData) {
            $exercises[] = new ExecutedExerciseDTO(
                $exerciseData['exercise_id'],
                $exerciseData['exercise_name'],
                $exerciseData['muscle_group'],
                $exerciseData['unique_reps'],
                $exerciseData['total_executions'],
                $exerciseData['first_executed_at'],
                $exerciseData['last_executed_at']
            );
        }

        return $exercises;
    }
}
