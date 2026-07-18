<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Application\Statistics\DTOs\ExecutedExerciseDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GetStudentExecutedExercisesUseCase
{
    public function execute(string $studentId): array
    {
        // Guard: studentId must be a valid UUID
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $studentId)) {
            throw new InvalidArgumentException('Invalid student ID format');
        }

        // Query to get all exercises executed by student in finished sessions
        $results = DB::table('set_executions as se')
            ->join('exercises as e', 'se.exercise_id', '=', 'e.id')
            ->join('muscle_groups as mg', 'e.muscle_group_id', '=', 'mg.id')
            ->join('workout_sessions as ws', 'se.workout_session_id', '=', 'ws.id')
            ->where('ws.student_id', $studentId)
            ->whereNotNull('ws.finished_at')
            ->select([
                'e.id as exercise_id',
                'e.name as exercise_name',
                'mg.name as muscle_group',
                DB::raw('COUNT(*) as total_executions'),
                DB::raw('MIN(se.completed_at) as first_executed_at'),
                DB::raw('MAX(se.completed_at) as last_executed_at'),
            ])
            ->groupBy('e.id', 'e.name', 'mg.name')
            ->orderBy('e.name', 'asc')
            ->get();

        // For each exercise, get unique reps
        $exercises = [];
        foreach ($results as $row) {
            // Get unique reps for this exercise
            $reps = DB::table('set_executions as se')
                ->join('workout_sessions as ws', 'se.workout_session_id', '=', 'ws.id')
                ->where('ws.student_id', $studentId)
                ->where('se.exercise_id', $row->exercise_id)
                ->whereNotNull('ws.finished_at')
                ->distinct()
                ->pluck('se.reps_completed')
                ->sort()
                ->values()
                ->toArray();

            $exercises[] = new ExecutedExerciseDTO(
                $row->exercise_id,
                $row->exercise_name,
                $row->muscle_group,
                $reps,
                (int) $row->total_executions,
                $row->first_executed_at,
                $row->last_executed_at
            );
        }

        return $exercises;
    }
}
