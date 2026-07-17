<?php

declare(strict_types=1);

namespace App\Domain\ExerciseWeightHistory\Entities;

use App\Domain\ExerciseWeightHistory\ValueObjects\ExerciseWeightHistoryId;
use App\Domain\ExerciseWeightHistory\ValueObjects\Weight;
use App\Domain\ExerciseWeightHistory\ValueObjects\Reps;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;

class ExerciseWeightHistoryEntity
{
    /** @var ExerciseWeightHistoryId */
    private $id;

    /** @var UserId */
    private $studentId;

    /** @var ExerciseId */
    private $exerciseId;

    /** @var Reps */
    private $reps;

    /** @var Weight */
    private $weight;

    /** @var \DateTimeImmutable */
    private $lastUsedAt;

    public function __construct(
        ExerciseWeightHistoryId $id,
        UserId $studentId,
        ExerciseId $exerciseId,
        Reps $reps,
        Weight $weight,
        \DateTimeImmutable $lastUsedAt
    ) {
        $this->id = $id;
        $this->studentId = $studentId;
        $this->exerciseId = $exerciseId;
        $this->reps = $reps;
        $this->weight = $weight;
        $this->lastUsedAt = $lastUsedAt;
    }

    public function getId(): ExerciseWeightHistoryId
    {
        return $this->id;
    }

    public function getStudentId(): UserId
    {
        return $this->studentId;
    }

    public function getExerciseId(): ExerciseId
    {
        return $this->exerciseId;
    }

    public function getReps(): Reps
    {
        return $this->reps;
    }

    public function getWeight(): Weight
    {
        return $this->weight;
    }

    public function getLastUsedAt(): \DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function updateWeight(Weight $newWeight): void
    {
        if (!$this->weight->equals($newWeight)) {
            $this->weight = $newWeight;
            $this->lastUsedAt = new \DateTimeImmutable();
        }
    }

    public function shouldUpdate(Weight $newWeight): bool
    {
        return !$this->weight->equals($newWeight);
    }
}
