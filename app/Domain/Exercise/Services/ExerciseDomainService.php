<?php

declare(strict_types=1);

namespace App\Domain\Exercise\Services;

use App\Domain\Exercise\Entities\ExerciseEntity;
use App\Domain\User\ValueObjects\UserId;

class ExerciseDomainService
{
    public function canTrainerEditExercise(ExerciseEntity $exercise, UserId $trainerId): bool
    {
        if (!$exercise->isEditable()) {
            return false;
        }

        return $exercise->belongsToTrainer($trainerId);
    }

    public function canTrainerDeleteExercise(ExerciseEntity $exercise, UserId $trainerId): bool
    {
        if (!$exercise->isDeletable()) {
            return false;
        }

        return $exercise->belongsToTrainer($trainerId);
    }

    public function canTrainerToggleExercise(ExerciseEntity $exercise): bool
    {
        return $exercise->canBeToggled();
    }
}
