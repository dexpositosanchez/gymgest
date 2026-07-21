<?php

declare(strict_types=1);

namespace App\Domain\Exercise\Repositories;

use App\Domain\Exercise\Entities\ExerciseEntity;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;
use App\Application\Exercise\DTOs\ExerciseFilterDTO;

interface ExerciseRepositoryInterface
{
    public function findById(ExerciseId $id): ?ExerciseEntity;

    /**
     * @param UserId $trainerId
     * @param ExerciseFilterDTO $filters
     * @return ExerciseEntity[]
     */
    public function findByTrainerWithPreferences(UserId $trainerId, ExerciseFilterDTO $filters): array;

    public function save(ExerciseEntity $exercise): void;

    public function delete(ExerciseId $id): void;

    /**
     * Get muscle group name for an exercise
     * Returns null if exercise doesn't exist or has no muscle group
     *
     * @param ExerciseId $exerciseId
     * @return string|null
     */
    public function getMuscleGroupName(ExerciseId $exerciseId): ?string;
}
