<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Application\Statistics\DTOs\RoutineStatsDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GetStudentRoutineStatsUseCase
{
    public function execute(string $studentId): array
    {
        if (empty($studentId)) {
            throw new InvalidArgumentException('Student ID is required');
        }

        // Query workout_sessions with finished_at NOT NULL, grouped by routine_assignment_id
        $stats = DB::table('workout_sessions as ws')
            ->join('routine_assignments as ra', 'ws.routine_assignment_id', '=', 'ra.id')
            ->join('routines as r', 'ra.routine_id', '=', 'r.id')
            ->where('ws.student_id', $studentId)
            ->whereNotNull('ws.finished_at')
            ->select([
                'r.id as routine_id',
                'r.name as routine_name',
                DB::raw('COUNT(*) as times_executed'),
                DB::raw('MIN(ws.finished_at) as first_session_at'),
                DB::raw('MAX(ws.finished_at) as last_session_at')
            ])
            ->groupBy('r.id', 'r.name')
            ->get();

        // Convert to DTOs
        return array_map(function ($stat) {
            return new RoutineStatsDTO(
                $stat->routine_id,
                $stat->routine_name,
                (int) $stat->times_executed,
                $stat->first_session_at,
                $stat->last_session_at
            );
        }, $stats->toArray());
    }
}
