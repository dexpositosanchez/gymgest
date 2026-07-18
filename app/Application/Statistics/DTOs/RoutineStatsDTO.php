<?php

declare(strict_types=1);

namespace App\Application\Statistics\DTOs;

class RoutineStatsDTO
{
    public string $routine_id;
    public string $routine_name;
    public int $times_executed;
    public string $first_session_at;
    public string $last_session_at;

    public function __construct(
        string $routine_id,
        string $routine_name,
        int $times_executed,
        string $first_session_at,
        string $last_session_at
    ) {
        $this->routine_id = $routine_id;
        $this->routine_name = $routine_name;
        $this->times_executed = $times_executed;
        $this->first_session_at = $first_session_at;
        $this->last_session_at = $last_session_at;
    }
}
