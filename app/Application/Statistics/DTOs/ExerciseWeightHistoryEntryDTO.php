<?php

declare(strict_types=1);

namespace App\Application\Statistics\DTOs;

class ExerciseWeightHistoryEntryDTO
{
    public string $date;
    public float $weight;
    public int $reps;

    public function __construct(
        string $date,
        float $weight,
        int $reps
    ) {
        $this->date = $date;
        $this->weight = $weight;
        $this->reps = $reps;
    }
}
