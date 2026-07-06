<?php

declare(strict_types=1);

namespace App\Application\Exercise\UseCases;

use App\Application\Exercise\DTOs\ExerciseResponseDTO;
use App\Application\Exercise\DTOs\MuscleGroupResponseDTO;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\Repositories\MuscleGroupRepositoryInterface;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;

class GetExerciseDetailsUseCase
{
    /** @var ExerciseRepositoryInterface */
    private $exerciseRepository;

    /** @var MuscleGroupRepositoryInterface */
    private $muscleGroupRepository;

    public function __construct(
        ExerciseRepositoryInterface $exerciseRepository,
        MuscleGroupRepositoryInterface $muscleGroupRepository
    ) {
        $this->exerciseRepository = $exerciseRepository;
        $this->muscleGroupRepository = $muscleGroupRepository;
    }

    public function execute(ExerciseId $id, UserId $trainerId): ?ExerciseResponseDTO
    {
        $exercise = $this->exerciseRepository->findById($id);

        if (!$exercise) {
            return null;
        }

        // Verificar que el ejercicio es accesible por el trainer
        // - Si es default, es accesible por todos
        // - Si es custom, solo por su creador
        if ($exercise->isCustom() && !$exercise->belongsToTrainer($trainerId)) {
            return null;
        }

        $muscleGroup = $this->muscleGroupRepository->findById($exercise->getMuscleGroupId());

        if (!$muscleGroup) {
            return null;
        }

        $muscleGroupDTO = new MuscleGroupResponseDTO(
            $muscleGroup->getId()->getValue(),
            $muscleGroup->getName()->getValue(),
            $muscleGroup->getDescription()
        );

        $isActive = true; // Default activo o según preferencia

        return new ExerciseResponseDTO(
            $exercise->getId()->getValue(),
            $exercise->getName()->getValue(),
            $exercise->getDescription()->getValue(),
            $muscleGroupDTO,
            $exercise->getType()->getValue(),
            $isActive,
            date('Y-m-d H:i:s')
        );
    }
}
