<?php

declare(strict_types=1);

namespace App\Application\Statistics\DTOs;

class ActiveStudentsStatsDTO
{
    public int $total_active_students;
    public array $students;

    public function __construct(
        int $total_active_students,
        array $students
    ) {
        $this->total_active_students = $total_active_students;
        $this->students = $students;
    }
}
