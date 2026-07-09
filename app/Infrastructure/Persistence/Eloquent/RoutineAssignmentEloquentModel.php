<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class RoutineAssignmentEloquentModel extends Model
{
    protected $table = 'routine_assignments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'routine_id',
        'student_id',
        'gym_id',
        'assigned_at',
        'starts_at',
        'is_current',
        'notes',
    ];

    protected $casts = [
        'id' => 'string',
        'routine_id' => 'string',
        'student_id' => 'string',
        'gym_id' => 'string',
        'assigned_at' => 'datetime',
        'starts_at' => 'date',
        'is_current' => 'boolean',
    ];

    public function routine()
    {
        return $this->belongsTo(RoutineEloquentModel::class, 'routine_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(UserEloquentModel::class, 'student_id', 'id');
    }

    public function gym()
    {
        return $this->belongsTo(GymEloquentModel::class, 'gym_id', 'id');
    }
}
