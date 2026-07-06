<?php

declare(strict_types=1);

namespace App\Application\Exercise\UseCases;

use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\Services\ExerciseDomainService;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;

class DeleteCustomExerciseUseCase
{
    /** @var ExerciseRepositoryInterface */
    private $exerciseRepository;

    /** @var ExerciseDomainService */
    private $exerciseDomainService;

    public function __construct(
        ExerciseRepositoryInterface $exerciseRepository,
        ExerciseDomainService $exerciseDomainService
    ) {
        $this->exerciseRepository = $exerciseRepository;
        $this->exerciseDomainService = $exerciseDomainService;
    }

    public function execute(ExerciseId $id, UserId $trainerId): void
    {
        $exercise = $this->exerciseRepository->findById($id);

        if (!$exercise) {
            throw new \DomainException('Ejercicio no encontrado');
        }

        if (!$this->exerciseDomainService->canTrainerDeleteExercise($exercise, $trainerId)) {
            throw new \DomainException('No tienes permiso para eliminar este ejercicio');
        }

        $this->exerciseRepository->delete($id);
    }
}
