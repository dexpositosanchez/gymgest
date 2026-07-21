<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Password;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Mappers\UserMapper;

class UserEloquentRepository implements UserRepositoryInterface
{
    public function findById(UserId $id): ?UserEntity
    {
        $model = UserEloquentModel::find($id->getValue());

        return $model ? UserMapper::toDomain($model) : null;
    }

    public function findByEmail(Email $email): ?UserEntity
    {
        $model = UserEloquentModel::where('email', $email->getValue())->first();

        return $model ? UserMapper::toDomain($model) : null;
    }

    public function save(UserEntity $user): UserEntity
    {
        $model = UserEloquentModel::find($user->getId()->getValue());

        if ($model) {
            UserMapper::updateEloquentFromDomain($model, $user);
        } else {
            $model = UserMapper::toEloquent($user);
        }

        $model->save();

        return UserMapper::toDomain($model);
    }

    public function existsByEmail(Email $email): bool
    {
        return UserEloquentModel::where('email', $email->getValue())->exists();
    }

    public function delete(UserId $id): void
    {
        UserEloquentModel::destroy($id->getValue());
    }

    public function updatePassword(UserId $userId, Password $newPassword): void
    {
        $model = UserEloquentModel::find($userId->getValue());

        if ($model === null) {
            throw new \DomainException('User not found');
        }

        $model->password = $newPassword->getHashedValue();
        $model->save();
    }

    public function markEmailAsVerified(UserId $userId): void
    {
        $model = UserEloquentModel::find($userId->getValue());

        if ($model === null) {
            throw new \DomainException('User not found');
        }

        $model->email_verified_at = now();
        $model->save();
    }
}