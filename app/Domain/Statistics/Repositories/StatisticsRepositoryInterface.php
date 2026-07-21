<?php

declare(strict_types=1);

namespace App\Domain\Statistics\Repositories;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\Exercise\ValueObjects\ExerciseId;

interface StatisticsRepositoryInterface
{
    /**
     * Get gym active students with their active workout sessions
     * Returns array with: student_id, student_name, last_workout_at
     *
     * @param GymId $gymId
     * @param UserId $trainerId
     * @return array
     */
    public function getGymActiveStudents(GymId $gymId, UserId $trainerId): array;

    /**
     * Count gym active students with active workout sessions
     *
     * @param GymId $gymId
     * @param UserId $studentId
     * @return int
     */
    public function countGymActiveStudents(GymId $gymId, UserId $studentId): int;

    /**
     * Get exercise weight history for a student by exercise and reps
     * Returns array with: date, weight, reps
     *
     * @param UserId $studentId
     * @param ExerciseId $exerciseId
     * @param int $reps
     * @return array
     */
    public function getExerciseWeightHistory(UserId $studentId, ExerciseId $exerciseId, int $reps): array;

    /**
     * Get all exercises executed by student in finished sessions
     * Returns array with: exercise_id, exercise_name, muscle_group, unique_reps, total_executions, first_executed_at, last_executed_at
     *
     * @param UserId $studentId
     * @return array
     */
    public function getStudentExecutedExercises(UserId $studentId): array;

    /**
     * Get routine stats for a student (finished sessions grouped by routine)
     * Returns array with: routine_id, routine_name, times_executed, first_session_at, last_session_at
     *
     * @param UserId $studentId
     * @return array
     */
    public function getStudentRoutineStats(UserId $studentId): array;
}
