<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSessionEloquentModel extends Model
{
    protected $table = 'workout_sessions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'routine_assignment_id',
        'student_id',
        'day_number',
        'started_at',
        'finished_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function routineAssignment(): BelongsTo
    {
        return $this->belongsTo(RoutineAssignmentEloquentModel::class, 'routine_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'student_id');
    }

    public function setExecutions(): HasMany
    {
        return $this->hasMany(SetExecutionEloquentModel::class, 'workout_session_id');
    }
}
