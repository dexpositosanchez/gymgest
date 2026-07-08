<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymStudentEloquentModel extends Model
{
    protected $table = 'gym_students';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'gym_id',
        'student_id',
        'quota_expires_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'quota_expires_at' => 'date:Y-m-d',
    ];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(GymEloquentModel::class, 'gym_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'student_id');
    }
}
