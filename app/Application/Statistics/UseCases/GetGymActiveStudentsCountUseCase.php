<?php

declare(strict_types=1);

namespace App\Application\Statistics\UseCases;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GetGymActiveStudentsCountUseCase
{
    public function execute(string $gymId, string $studentId): int
    {
        if (empty($gymId)) {
            throw new InvalidArgumentException('Gym ID is required');
        }

        if (empty($studentId)) {
            throw new InvalidArgumentException('Student ID is required');
        }

        // Verify student is enrolled in this gym
        $enrollment = DB::table('gym_students')
            ->where('gym_id', $gymId)
            ->where('student_id', $studentId)
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
            ->where('gs.gym_id', $gymId)
            ->where('gs.is_active', true)
            ->distinct('gs.student_id')
            ->count('gs.student_id');

        return (int) $count;
    }
}
