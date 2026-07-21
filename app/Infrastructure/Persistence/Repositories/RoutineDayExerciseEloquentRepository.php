<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Routine\Entities\RoutineDayExerciseEntity;
use App\Domain\Routine\Repositories\RoutineDayExerciseRepositoryInterface;
use App\Domain\Routine\ValueObjects\DayNumber;
use App\Domain\Routine\ValueObjects\RoutineId;
use App\Domain\WorkoutSession\ValueObjects\WorkoutSessionId;
use App\Domain\Exercise\ValueObjects\ExerciseId;
use App\Infrastructure\Persistence\Eloquent\WorkoutSessionEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineAssignmentEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineDayExerciseEloquentModel;
use App\Infrastructure\Persistence\Mappers\RoutineDayExerciseMapper;
use Illuminate\Support\Facades\DB;

class RoutineDayExerciseEloquentRepository implements RoutineDayExerciseRepositoryInterface
{
    public function findBySessionAndExercise(
        WorkoutSessionId $sessionId,
        ExerciseId $exerciseId
    ): ?RoutineDayExerciseEntity {
        // Get session
        $session = WorkoutSessionEloquentModel::find($sessionId->getValue());
        if ($session === null) {
            return null;
        }

        // Get assignment
        $assignment = RoutineAssignmentEloquentModel::find($session->routine_assignment_id);
        if ($assignment === null) {
            return null;
        }

        // Find routine day exercise
        $model = RoutineDayExerciseEloquentModel::whereHas('routineDay', function ($query) use ($assignment, $session) {
            $query->where('routine_id', $assignment->routine_id)
                  ->where('day_number', $session->day_number);
        })
        ->where('exercise_id', $exerciseId->getValue())
        ->first();

        if ($model === null) {
            return null;
        }

        return RoutineDayExerciseMapper::toDomain($model);
    }

    public function getExercisesWithDetailsForDay(RoutineId $routineId, DayNumber $dayNumber): array
    {
        // Pragmatic approach: use query with joins to get all data in one query (performance)
        $results = DB::table('routine_day_exercises as rde')
            ->join('routine_days as rd', 'rde.routine_day_id', '=', 'rd.id')
            ->join('exercises as e', 'rde.exercise_id', '=', 'e.id')
            ->leftJoin('routine_day_exercise_sets as rdes', 'rde.id', '=', 'rdes.routine_day_exercise_id')
            ->where('rd.routine_id', $routineId->getValue())
            ->where('rd.day_number', $dayNumber->getValue())
            ->select([
                'e.id as exercise_id',
                'e.name as exercise_name',
                DB::raw('COUNT(rdes.id) as total_sets')
            ])
            ->groupBy('e.id', 'e.name', 'rde.order_index')
            ->orderBy('rde.order_index', 'asc')
            ->get();

        return $results->map(function ($row) {
            return [
                'exercise_id' => $row->exercise_id,
                'exercise_name' => $row->exercise_name,
                'total_sets' => (int) $row->total_sets,
            ];
        })->toArray();
    }
}
