<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class TrainerExercisePreferenceEloquentModel extends Model
{
    protected $table = 'trainer_exercise_preferences';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'trainer_id',
        'exercise_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function trainer()
    {
        return $this->belongsTo(\App\Infrastructure\Persistence\Eloquent\UserEloquentModel::class, 'trainer_id');
    }

    public function exercise()
    {
        return $this->belongsTo(ExerciseEloquentModel::class, 'exercise_id');
    }
}
