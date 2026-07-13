<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\RoutineAssignment\Services\RoutineAssignmentCacheService;
use App\Domain\Gym\ValueObjects\GymId;
use App\Domain\GymStudent\Entities\GymStudentEntity;
use App\Domain\GymStudent\Repositories\GymStudentRepositoryInterface;
use App\Domain\GymStudent\ValueObjects\GymStudentId;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use App\Infrastructure\Persistence\Mappers\GymStudentMapper;
use Illuminate\Support\Facades\DB;

class GymStudentEloquentRepository implements GymStudentRepositoryInterface
{
    private GymStudentMapper $mapper;
    private RoutineAssignmentCacheService $cacheService;

    public function __construct(
        GymStudentMapper $mapper,
        RoutineAssignmentCacheService $cacheService
    ) {
        $this->mapper = $mapper;
        $this->cacheService = $cacheService;
    }

    public function save(GymStudentEntity $gymStudent): void
    {
        $model = $this->mapper->toEloquent($gymStudent);
        $model->save();

        // Invalidate routine cache when gym_student relationship changes
        $this->cacheService->invalidate($gymStudent->getStudentId()->getValue());
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

    public function findActiveGymsByStudent(UserId $studentId): array
    {
        return DB::table('gym_students')
            ->join('gyms', 'gym_students.gym_id', '=', 'gyms.id')
            ->join('users', 'gyms.trainer_id', '=', 'users.id')
            ->select(
                'gym_students.id as enrollment_id',
                'gym_students.created_at as enrolled_at',
                'gym_students.quota_expires_at',
                'gyms.id as gym_id',
                'gyms.name as gym_name',
                'gyms.address as gym_address',
                'gyms.locality as gym_locality',
                'gyms.province as gym_province',
                'gyms.country as gym_country',
                'gyms.is_personal_training',
                'users.id as trainer_id',
                'users.name as trainer_name',
                'users.last_name as trainer_last_name',
                'users.email as trainer_email'
            )
            ->where('gym_students.student_id', $studentId->getValue())
            ->where('gym_students.is_active', true)
            ->where('gyms.is_active', true)
            ->orderBy('gym_students.created_at', 'DESC')
            ->get()
            ->toArray();
    }
}
