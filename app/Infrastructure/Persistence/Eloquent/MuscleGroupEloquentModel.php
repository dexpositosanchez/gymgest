<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class MuscleGroupEloquentModel extends Model
{
    protected $table = 'muscle_groups';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
    ];

    public function exercises()
    {
        return $this->hasMany(ExerciseEloquentModel::class, 'muscle_group_id');
    }
}
