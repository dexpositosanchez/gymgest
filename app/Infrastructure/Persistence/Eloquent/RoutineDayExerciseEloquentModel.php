<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class RoutineDayExerciseEloquentModel extends Model
{
    protected $table = 'routine_day_exercises';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'routine_day_id',
        'exercise_id',
        'order_index',
        'notes',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function routineDay()
    {
        return $this->belongsTo(RoutineDayEloquentModel::class, 'routine_day_id');
    }

    public function exercise()
    {
        return $this->belongsTo(ExerciseEloquentModel::class, 'exercise_id');
    }

    public function sets()
    {
        return $this->hasMany(ExerciseSetEloquentModel::class, 'routine_day_exercise_id');
    }
}
