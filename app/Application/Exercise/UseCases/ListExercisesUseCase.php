<?php

declare(strict_types=1);

namespace App\Application\Exercise\UseCases;

use App\Application\Exercise\DTOs\ExerciseFilterDTO;
use App\Application\Exercise\DTOs\ExerciseResponseDTO;
use App\Application\Exercise\DTOs\MuscleGroupResponseDTO;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\Repositories\MuscleGroupRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class ListExercisesUseCase
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

    /**
     * @param UserId $trainerId
     * @param ExerciseFilterDTO $filters
     * @return ExerciseResponseDTO[]
     */
    public function execute(UserId $trainerId, ExerciseFilterDTO $filters): array
    {
        $exercises = $this->exerciseRepository->findByTrainerWithPreferences($trainerId, $filters);

        $responseList = [];
        foreach ($exercises as $exercise) {
            $muscleGroup = $this->muscleGroupRepository->findById($exercise->getMuscleGroupId());

            if (!$muscleGroup) {
                continue;
            }

            $muscleGroupDTO = new MuscleGroupResponseDTO(
                $muscleGroup->getId()->getValue(),
                $muscleGroup->getName()->getValue(),
                $muscleGroup->getDescription()
            );

            // Read is_active from preference metadata (attached by Repository)
            $isActive = property_exists($exercise, 'preferenceIsActive')
                ? ($exercise->preferenceIsActive ?? true)
                : true;

            $dto = new ExerciseResponseDTO(
                $exercise->getId()->getValue(),
                $exercise->getName()->getValue(),
                $exercise->getDescription()->getValue(),
                $muscleGroupDTO,
                $exercise->getType()->getValue(),
                $isActive,
                date('Y-m-d H:i:s') // Timestamp placeholder - debería venir de la entidad si existe
            );

            // Attach entity for Controller to access metadata
            $dto->entity = $exercise;

            $responseList[] = $dto;
        }

        return $responseList;
    }
}
