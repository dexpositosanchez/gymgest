<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\User\Entities\UserEntity;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserType;
use App\Domain\User\ValueObjects\Password;
use App\Domain\User\ValueObjects\BirthDate;
use App\Domain\User\ValueObjects\Gender;
use App\Domain\User\ValueObjects\PersonName;
use App\Domain\User\ValueObjects\GymGoals;
use App\Domain\User\ValueObjects\EmailVerifiedAt;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;

class UserMapper
{
    public static function toDomain(UserEloquentModel $model): UserEntity
    {
        $birthDateString = $model->birth_date instanceof \Carbon\Carbon
            ? $model->birth_date->toDateString()
            : (string) $model->birth_date;

        $emailVerifiedAt = $model->email_verified_at !== null
            ? EmailVerifiedAt::fromString($model->email_verified_at->format('Y-m-d H:i:s'))
            : new EmailVerifiedAt(null);

        return new UserEntity(
            new UserId($model->id),
            new Email($model->email),
            Password::fromHashed($model->password),
            new UserType($model->user_type),
            new PersonName($model->name),
            new PersonName($model->last_name),
            new BirthDate($birthDateString),
            new Gender($model->gender),
            new GymGoals($model->gym_goals),
            $emailVerifiedAt
        );
    }

    public static function toEloquent(UserEntity $entity): UserEloquentModel
    {
        $model = new UserEloquentModel();
        $model->id = $entity->getId()->getValue();
        $model->email = $entity->getEmail()->getValue();
        $model->password = $entity->getPassword()->getHashedValue();
        $model->user_type = $entity->getUserType()->getValue();
        $model->name = $entity->getName()->getValue();
        $model->last_name = $entity->getLastName()->getValue();
        $model->birth_date = $entity->getBirthDate()->toString();
        $model->gender = $entity->getGender()->getValue();
        $model->gym_goals = $entity->getGymGoals()->getValue();
        $model->email_verified_at = $entity->getEmailVerifiedAt()->toString();

        return $model;
    }

    public static function updateEloquentFromDomain(UserEloquentModel $model, UserEntity $entity): void
    {
        $model->email = $entity->getEmail()->getValue();
        $model->password = $entity->getPassword()->getHashedValue();
        $model->user_type = $entity->getUserType()->getValue();
        $model->name = $entity->getName()->getValue();
        $model->last_name = $entity->getLastName()->getValue();
        $model->birth_date = $entity->getBirthDate()->toString();
        $model->gender = $entity->getGender()->getValue();
        $model->gym_goals = $entity->getGymGoals()->getValue();
        $model->email_verified_at = $entity->getEmailVerifiedAt()->toString();
    }
}
