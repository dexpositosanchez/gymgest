<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetExecutionEloquentModel extends Model
{
    protected $table = 'set_executions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'workout_session_id',
        'routine_day_exercise_id',
        'exercise_id',
        'set_number',
        'reps_completed',
        'weight_used',
        'completed_at',
    ];

    protected $casts = [
        'set_number' => 'integer',
        'reps_completed' => 'integer',
        'weight_used' => 'float',
        'completed_at' => 'datetime',
    ];

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSessionEloquentModel::class, 'workout_session_id');
    }

    public function routineDayExercise(): BelongsTo
    {
        return $this->belongsTo(RoutineDayExerciseEloquentModel::class, 'routine_day_exercise_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(ExerciseEloquentModel::class, 'exercise_id');
    }
}
