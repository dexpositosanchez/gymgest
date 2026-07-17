<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseWeightHistoryEloquentModel extends Model
{
    protected $table = 'exercise_weight_history';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'student_id',
        'exercise_id',
        'reps',
        'weight',
        'last_used_at',
    ];

    protected $casts = [
        'reps' => 'integer',
        'weight' => 'float',
        'last_used_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'student_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(ExerciseEloquentModel::class, 'exercise_id');
    }
}
