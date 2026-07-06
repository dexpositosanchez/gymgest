<?php

declare(strict_types=1);

namespace App\Application\Exercise\UseCases;

use App\Application\Exercise\DTOs\MuscleGroupResponseDTO;
use App\Domain\Exercise\Repositories\MuscleGroupRepositoryInterface;

class ListMuscleGroupsUseCase
{
    /** @var MuscleGroupRepositoryInterface */
    private $muscleGroupRepository;

    public function __construct(MuscleGroupRepositoryInterface $muscleGroupRepository)
    {
        $this->muscleGroupRepository = $muscleGroupRepository;
    }

    /**
     * @return MuscleGroupResponseDTO[]
     */
    public function execute(): array
    {
        $muscleGroups = $this->muscleGroupRepository->findAll();

        $responseList = [];
        foreach ($muscleGroups as $muscleGroup) {
            $responseList[] = new MuscleGroupResponseDTO(
                $muscleGroup->getId()->getValue(),
                $muscleGroup->getName()->getValue(),
                $muscleGroup->getDescription()
            );
        }

        return $responseList;
    }
}
