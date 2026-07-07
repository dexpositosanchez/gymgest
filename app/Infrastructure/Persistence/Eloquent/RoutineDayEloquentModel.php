<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class RoutineDayEloquentModel extends Model
{
    protected $table = 'routine_days';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'routine_id',
        'day_number',
        'name',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function routine()
    {
        return $this->belongsTo(RoutineEloquentModel::class, 'routine_id');
    }

    public function exercises()
    {
        return $this->hasMany(RoutineDayExerciseEloquentModel::class, 'routine_day_id');
    }
}
