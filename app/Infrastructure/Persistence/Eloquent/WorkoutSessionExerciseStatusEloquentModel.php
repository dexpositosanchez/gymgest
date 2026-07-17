<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSessionExerciseStatusEloquentModel extends Model
{
    protected $table = 'workout_session_exercise_status';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'workout_session_id',
        'exercise_id',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function workoutSession(): BelongsTo
    {
        return $this->belongsTo(WorkoutSessionEloquentModel::class, 'workout_session_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(ExerciseEloquentModel::class, 'exercise_id');
    }
}
