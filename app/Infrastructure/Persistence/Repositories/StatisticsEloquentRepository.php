<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Statistics\Repositories\StatisticsRepositoryInterface;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StatisticsEloquentRepository implements StatisticsRepositoryInterface
{
    public function getGymActiveStudents(GymId $gymId, UserId $trainerId): array
    {
        // Verify gym belongs to trainer
        $gym = DB::table('gyms')
            ->where('id', $gymId->getValue())
            ->first();

        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        if ($gym->trainer_id !== $trainerId->getValue()) {
            throw new InvalidArgumentException('Unauthorized');
        }

        // Get active students with their active workout session
        // Active students are those enrolled with is_active=true AND have ACTIVE (unfinished) sessions
        $activeStudents = DB::table('gym_students as gs')
            ->join('users as u', 'gs.student_id', '=', 'u.id')
            ->join('workout_sessions as ws', function ($join) {
                $join->on('ws.student_id', '=', 'gs.student_id')
                    ->whereNull('ws.finished_at'); // ACTIVE sessions only
            })
            ->where('gs.gym_id', $gymId->getValue())
            ->where('gs.is_active', true)
            ->select([
                'u.id as student_id',
                DB::raw("CONCAT(u.name, ' ', u.last_name) as student_name"),
                DB::raw('MAX(ws.started_at) as last_workout_at')
            ])
            ->groupBy('u.id', 'u.name', 'u.last_name')
            ->get();

        return array_map(function ($student) {
            return [
                'student_id' => $student->student_id,
                'student_name' => $student->student_name,
                'last_workout_at' => $student->last_workout_at,
            ];
        }, $activeStudents->toArray());
    }

    public function countGymActiveStudents(GymId $gymId, UserId $studentId): int
    {
        // Verify student is enrolled in this gym
        $enrollment = DB::table('gym_students')
            ->where('gym_id', $gymId->getValue())
            ->where('student_id', $studentId->getValue())
            ->where('is_active', true)
            ->first();

        if (!$enrollment) {
            throw new InvalidArgumentException('Unauthorized');
        }

        // Count active students with ACTIVE (unfinished) sessions
        $count = DB::table('gym_students as gs')
            ->join('workout_sessions as ws', function ($join) {
                $join->on('ws.student_id', '=', 'gs.student_id')
                    ->whereNull('ws.finished_at'); // ACTIVE sessions only
            })
            ->where('gs.gym_id', $gymId->getValue())
            ->where('gs.is_active', true)
            ->distinct('gs.student_id')
            ->count('gs.student_id');

        return (int) $count;
    }

    public function getExerciseWeightHistory(UserId $studentId, ExerciseId $exerciseId, int $reps): array
    {
        // Get all set executions ordered chronologically
        // Show each weight change as a new point in the graph
        $history = DB::table('set_executions as se')
            ->join('workout_sessions as ws', 'se.workout_session_id', '=', 'ws.id')
            ->where('ws.student_id', $studentId->getValue())
            ->where('se.exercise_id', $exerciseId->getValue())
            ->where('se.reps_completed', $reps)
            ->whereNotNull('se.weight_used')
            ->whereNotNull('ws.finished_at') // Only finished sessions
            ->select([
                DB::raw('DATE(se.completed_at) as date'),
                'se.weight_used as weight',
                'se.reps_completed as reps'
            ])
            ->orderBy('se.completed_at', 'ASC')
            ->get();

        // Filter to show only weight changes (remove consecutive duplicates)
        $filtered = [];
        $lastWeight = null;

        foreach ($history as $entry) {
            $currentWeight = (float) $entry->weight;

            // Add point only when weight changes
            if ($lastWeight === null || abs($currentWeight - $lastWeight) >= 0.01) {
                $filtered[] = [
                    'date' => $entry->date,
                    'weight' => $currentWeight,
                    'reps' => (int) $entry->reps,
                ];
                $lastWeight = $currentWeight;
            }
        }

        return $filtered;
    }

    public function getStudentExecutedExercises(UserId $studentId): array
    {
        // Query to get all exercises executed by student in finished sessions
        $results = DB::table('set_executions as se')
            ->join('exercises as e', 'se.exercise_id', '=', 'e.id')
            ->join('muscle_groups as mg', 'e.muscle_group_id', '=', 'mg.id')
            ->join('workout_sessions as ws', 'se.workout_session_id', '=', 'ws.id')
            ->where('ws.student_id', $studentId->getValue())
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
                ->where('ws.student_id', $studentId->getValue())
                ->where('se.exercise_id', $row->exercise_id)
                ->whereNotNull('ws.finished_at')
                ->distinct()
                ->pluck('se.reps_completed')
                ->sort()
                ->values()
                ->toArray();

            $exercises[] = [
                'exercise_id' => $row->exercise_id,
                'exercise_name' => $row->exercise_name,
                'muscle_group' => $row->muscle_group,
                'unique_reps' => $reps,
                'total_executions' => (int) $row->total_executions,
                'first_executed_at' => $row->first_executed_at,
                'last_executed_at' => $row->last_executed_at,
            ];
        }

        return $exercises;
    }

    public function getStudentRoutineStats(UserId $studentId): array
    {
        // Query workout_sessions with finished_at NOT NULL, grouped by routine_assignment_id
        $stats = DB::table('workout_sessions as ws')
            ->join('routine_assignments as ra', 'ws.routine_assignment_id', '=', 'ra.id')
            ->join('routines as r', 'ra.routine_id', '=', 'r.id')
            ->where('ws.student_id', $studentId->getValue())
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

        return array_map(function ($stat) {
            return [
                'routine_id' => $stat->routine_id,
                'routine_name' => $stat->routine_name,
                'times_executed' => (int) $stat->times_executed,
                'first_session_at' => $stat->first_session_at,
                'last_session_at' => $stat->last_session_at,
            ];
        }, $stats->toArray());
    }
}
