<?php

declare(strict_types=1);

namespace App\Domain\Exercise\Entities;

use App\Domain\Exercise\ValueObjects\PreferenceId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\User\ValueObjects\UserId;

class TrainerExercisePreferenceEntity
{
    /** @var PreferenceId */
    private $id;

    /** @var UserId */
    private $trainerId;

    /** @var ExerciseId */
    private $exerciseId;

    /** @var bool */
    private $isActive;

    public function __construct(
        PreferenceId $id,
        UserId $trainerId,
        ExerciseId $exerciseId,
        bool $isActive
    ) {
        $this->id = $id;
        $this->trainerId = $trainerId;
        $this->exerciseId = $exerciseId;
        $this->isActive = $isActive;
    }

    public function getId(): PreferenceId
    {
        return $this->id;
    }

    public function getTrainerId(): UserId
    {
        return $this->trainerId;
    }

    public function getExerciseId(): ExerciseId
    {
        return $this->exerciseId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }
}
