<?php

declare(strict_types=1);

namespace App\Domain\Exercise\Repositories;

use App\Domain\Exercise\Entities\TrainerExercisePreferenceEntity;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;

interface TrainerExercisePreferenceRepositoryInterface
{
    public function findByTrainerAndExercise(UserId $trainerId, ExerciseId $exerciseId): ?TrainerExercisePreferenceEntity;

    public function save(TrainerExercisePreferenceEntity $preference): void;
}
