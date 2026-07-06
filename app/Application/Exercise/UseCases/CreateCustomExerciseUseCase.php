<?php

declare(strict_types=1);

namespace App\Application\Exercise\UseCases;

use App\Application\Exercise\DTOs\CreateExerciseDTO;
use App\Domain\Exercise\Entities\ExerciseEntity;
use App\Domain\Exercise\Repositories\ExerciseRepositoryInterface;
use App\Domain\Exercise\ValueObjects\ExerciseDescription;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseName;
use App\Domain\Exercise\ValueObjects\ExerciseType;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Domain\User\ValueObjects\UserId;
use Ramsey\Uuid\Uuid;

class CreateCustomExerciseUseCase
{
    /** @var ExerciseRepositoryInterface */
    private $exerciseRepository;

    public function __construct(ExerciseRepositoryInterface $exerciseRepository)
    {
        $this->exerciseRepository = $exerciseRepository;
    }

    public function execute(CreateExerciseDTO $dto, UserId $trainerId): ExerciseEntity
    {
        $exercise = new ExerciseEntity(
            new ExerciseId(Uuid::uuid4()->toString()),
            new ExerciseName($dto->name),
            new ExerciseDescription($dto->description),
            new MuscleGroupId($dto->muscleGroupId),
            new ExerciseType(ExerciseType::CUSTOM),
            $trainerId
        );

        $this->exerciseRepository->save($exercise);

        return $exercise;
    }
}
