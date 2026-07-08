<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class GymEloquentModel extends Model
{
    protected $table = 'gyms';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'trainer_id',
        'name',
        'address',
        'locality',
        'province',
        'country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function trainer()
    {
        return $this->belongsTo(UserEloquentModel::class, 'trainer_id');
    }
}
