<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\UserEntity;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;

interface UserRepositoryInterface
{
    public function findById(UserId $id): ?UserEntity;

    public function findByEmail(Email $email): ?UserEntity;

    public function save(UserEntity $user): UserEntity;

    public function existsByEmail(Email $email): bool;

    public function delete(UserId $id): void;
}