<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use App\Application\Statistics\DTOs\ActiveStudentDTO;
use App\Application\Statistics\DTOs\ActiveStudentsStatsDTO;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GetGymActiveStudentsUseCase
{
    public function execute(string $gymId, string $trainerId): ActiveStudentsStatsDTO
    {
        if (empty($gymId)) {
            throw new InvalidArgumentException('Gym ID is required');
        }

        if (empty($trainerId)) {
            throw new InvalidArgumentException('Trainer ID is required');
        }

        // Verify gym belongs to trainer
        $gym = DB::table('gyms')
            ->where('id', $gymId)
            ->first();

        if (!$gym) {
            throw new InvalidArgumentException('Gym not found');
        }

        if ($gym->trainer_id !== $trainerId) {
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
            ->where('gs.gym_id', $gymId)
            ->where('gs.is_active', true)
            ->select([
                'u.id as student_id',
                DB::raw("CONCAT(u.name, ' ', u.last_name) as student_name"),
                DB::raw('MAX(ws.started_at) as last_workout_at')
            ])
            ->groupBy('u.id', 'u.name', 'u.last_name')
            ->get();

        $students = array_map(function ($student) {
            return new ActiveStudentDTO(
                $student->student_id,
                $student->student_name,
                $student->last_workout_at
            );
        }, $activeStudents->toArray());

        return new ActiveStudentsStatsDTO(
            count($students),
            $students
        );
    }
}
