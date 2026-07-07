<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class ExerciseSetEloquentModel extends Model
{
    protected $table = 'routine_day_exercise_sets';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'routine_day_exercise_id',
        'set_number',
        'reps',
        'rest_seconds',
        'notes',
    ];

    protected $casts = [
        'set_number' => 'integer',
        'reps' => 'integer',
        'rest_seconds' => 'integer',
    ];

    public function routineDayExercise()
    {
        return $this->belongsTo(RoutineDayExerciseEloquentModel::class, 'routine_day_exercise_id');
    }
}
