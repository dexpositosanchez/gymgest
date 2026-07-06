<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class ExerciseEloquentModel extends Model
{
    protected $table = 'exercises';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'muscle_group_id',
        'trainer_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function muscleGroup()
    {
        return $this->belongsTo(MuscleGroupEloquentModel::class, 'muscle_group_id');
    }

    public function trainer()
    {
        return $this->belongsTo(\App\Infrastructure\Persistence\Eloquent\UserEloquentModel::class, 'trainer_id');
    }

    public function preferences()
    {
        return $this->hasMany(TrainerExercisePreferenceEloquentModel::class, 'exercise_id');
    }
}
