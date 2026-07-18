<?php

declare(strict_types=1);

namespace App\Application\Statistics\DTOs;

class ActiveStudentDTO
{
    public string $student_id;
    public string $student_name;
    public string $last_workout_at;

    public function __construct(
        string $student_id,
        string $student_name,
        string $last_workout_at
    ) {
        $this->student_id = $student_id;
        $this->student_name = $student_name;
        $this->last_workout_at = $last_workout_at;
    }
}
