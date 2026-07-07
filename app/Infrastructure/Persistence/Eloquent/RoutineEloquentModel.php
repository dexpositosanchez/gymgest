<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class RoutineEloquentModel extends Model
{
    protected $table = 'routines';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'trainer_id',
        'name',
        'description',
        'difficulty',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function trainer()
    {
        return $this->belongsTo(UserEloquentModel::class, 'trainer_id');
    }

    public function days()
    {
        return $this->hasMany(RoutineDayEloquentModel::class, 'routine_id');
    }
}
