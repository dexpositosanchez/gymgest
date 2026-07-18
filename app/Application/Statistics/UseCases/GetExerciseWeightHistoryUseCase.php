<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Application\Statistics\DTOs\ExerciseWeightHistoryEntryDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GetExerciseWeightHistoryUseCase
{
    public function execute(string $studentId, string $exerciseId, int $reps): array
    {
        if (empty($studentId)) {
            throw new InvalidArgumentException('Student ID is required');
        }

        if (empty($exerciseId)) {
            throw new InvalidArgumentException('Exercise ID is required');
        }

        if ($reps <= 0) {
            throw new InvalidArgumentException('Reps must be greater than 0');
        }

        // Query set_executions joined with workout_sessions
        // Filter by student, exercise, and reps, order by completed_at ASC
        $history = DB::table('set_executions as se')
            ->join('workout_sessions as ws', 'se.workout_session_id', '=', 'ws.id')
            ->where('ws.student_id', $studentId)
            ->where('se.exercise_id', $exerciseId)
            ->where('se.reps_completed', $reps)
            ->whereNotNull('se.completed_at')
            ->select([
                'se.completed_at as date',
                'se.weight_used as weight',
                'se.reps_completed as reps'
            ])
            ->orderBy('se.completed_at', 'ASC')
            ->get();

        // Convert to DTOs
        return array_map(function ($entry) {
            return new ExerciseWeightHistoryEntryDTO(
                $entry->date,
                (float) $entry->weight,
                (int) $entry->reps
            );
        }, $history->toArray());
    }
}
