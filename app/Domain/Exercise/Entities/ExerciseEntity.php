<?php

declare(strict_types=1);

namespace App\Domain\Exercise\Entities;

use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Domain\Exercise\ValueObjects\ExerciseName;
use App\Domain\Exercise\ValueObjects\ExerciseDescription;
use App\Domain\Exercise\ValueObjects\MuscleGroupId;
use App\Domain\Exercise\ValueObjects\ExerciseType;
use App\Domain\User\ValueObjects\UserId;

class ExerciseEntity
{
    /** @var ExerciseId */
    private $id;

    /** @var ExerciseName */
    private $name;

    /** @var ExerciseDescription */
    private $description;

    /** @var MuscleGroupId */
    private $muscleGroupId;

    /** @var UserId|null */
    private $trainerId;

    /** @var ExerciseType */
    private $type;

    public function __construct(
        ExerciseId $id,
        ExerciseName $name,
        ExerciseDescription $description,
        MuscleGroupId $muscleGroupId,
        ExerciseType $type,
        ?UserId $trainerId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->muscleGroupId = $muscleGroupId;
        $this->type = $type;
        $this->trainerId = $trainerId;
    }

    public function getId(): ExerciseId
    {
        return $this->id;
    }

    public function getName(): ExerciseName
    {
        return $this->name;
    }

    public function getDescription(): ExerciseDescription
    {
        return $this->description;
    }

    public function getMuscleGroupId(): MuscleGroupId
    {
        return $this->muscleGroupId;
    }

    public function getTrainerId(): ?UserId
    {
        return $this->trainerId;
    }

    public function getType(): ExerciseType
    {
        return $this->type;
    }

    public function isDefault(): bool
    {
        return $this->type->isDefault();
    }

    public function isCustom(): bool
    {
        return $this->type->isCustom();
    }

    public function belongsToTrainer(UserId $trainerId): bool
    {
        if ($this->trainerId === null) {
            return false;
        }

        return $this->trainerId->equals($trainerId);
    }

    public function isEditable(): bool
    {
        return $this->isCustom() && $this->trainerId !== null;
    }

    public function isDeletable(): bool
    {
        return $this->isCustom() && $this->trainerId !== null;
    }

    public function canBeToggled(): bool
    {
        return $this->isDefault();
    }

    public function updateName(ExerciseName $name): void
    {
        $this->name = $name;
    }

    public function updateDescription(ExerciseDescription $description): void
    {
        $this->description = $description;
    }

    public function updateMuscleGroup(MuscleGroupId $muscleGroupId): void
    {
        $this->muscleGroupId = $muscleGroupId;
    }
}
