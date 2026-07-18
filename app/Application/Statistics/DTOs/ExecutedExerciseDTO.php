<?php

declare(strict_types=1);

namespace App\Application\Statistics\DTOs;

class ExecutedExerciseDTO
{
    public string $exercise_id;
    public string $exercise_name;
    public string $muscle_group;
    public array $reps_available;
    public int $total_executions;
    public string $first_executed_at;
    public string $last_executed_at;

    public function __construct(
        string $exercise_id,
        string $exercise_name,
        string $muscle_group,
        array $reps_available,
        int $total_executions,
        string $first_executed_at,
        string $last_executed_at
    ) {
        $this->exercise_id = $exercise_id;
        $this->exercise_name = $exercise_name;
        $this->muscle_group = $muscle_group;
        $this->reps_available = $reps_available;
        $this->total_executions = $total_executions;
        $this->first_executed_at = $first_executed_at;
        $this->last_executed_at = $last_executed_at;
    }
}
