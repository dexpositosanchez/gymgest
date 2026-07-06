<?php

declare(strict_types=1);

namespace App\Application\Exercise\UseCases;

use App\Application\Exercise\DTOs\UpdateExerciseDTO;
use App\Domain\Exercise\Entities\ExerciseEntity;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\Services\ExerciseDomainService;
use App\Domain\Exercise\ValueObjects\ExerciseDescription;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseName;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Domain\User\ValueObjects\UserId;

class UpdateCustomExerciseUseCase
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

    public function execute(ExerciseId $id, UpdateExerciseDTO $dto, UserId $trainerId): ExerciseEntity
    {
        $exercise = $this->exerciseRepository->findById($id);

        if (!$exercise) {
            throw new \DomainException('Ejercicio no encontrado');
        }

        if (!$this->exerciseDomainService->canTrainerEditExercise($exercise, $trainerId)) {
            throw new \DomainException('No tienes permiso para editar este ejercicio');
        }

        // Crear nueva entidad con datos actualizados
        $updatedExercise = new ExerciseEntity(
            $exercise->getId(),
            new ExerciseName($dto->name),
            new ExerciseDescription($dto->description),
            new MuscleGroupId($dto->muscleGroupId),
            $exercise->getType(),
            $exercise->getTrainerId()
        );

        $this->exerciseRepository->save($updatedExercise);

        return $updatedExercise;
    }
}
