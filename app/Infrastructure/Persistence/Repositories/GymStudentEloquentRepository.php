<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Entities\GymStudentEntity;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\GymStudent\ValueObjects\GymStudentId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use App\Infrastructure\Persistence\Mappers\GymStudentMapper;

class GymStudentEloquentRepository implements GymStudentRepositoryInterface
{
    private GymStudentMapper $mapper;

    public function __construct(GymStudentMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function save(GymStudentEntity $gymStudent): void
    {
        $model = $this->mapper->toEloquent($gymStudent);
        $model->save();
    }

    public function findById(GymStudentId $id): ?GymStudentEntity
    {
        $model = GymStudentEloquentModel::find($id->getValue());

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByGymAndStudent(GymId $gymId, UserId $studentId): ?GymStudentEntity
    {
        $model = GymStudentEloquentModel::where('gym_id', $gymId->getValue())
            ->where('student_id', $studentId->getValue())
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByGymId(GymId $gymId): array
    {
        $models = GymStudentEloquentModel::where('gym_id', $gymId->getValue())
            ->get();

        return $models->map(fn($model) => $this->mapper->toDomain($model))->toArray();
    }

    public function findByTrainerId(UserId $trainerId): array
    {
        $models = GymStudentEloquentModel::whereHas('gym', function ($query) use ($trainerId) {
            $query->where('trainer_id', $trainerId->getValue());
        })->get();

        return $models->map(fn($model) => $this->mapper->toDomain($model))->toArray();
    }

    public function delete(GymStudentId $id): void
    {
        GymStudentEloquentModel::destroy($id->getValue());
    }

    public function countActiveByGym(GymId $gymId): int
    {
        return GymStudentEloquentModel::where('gym_id', $gymId->getValue())
            ->where('is_active', true)
            ->count();
    }
}
