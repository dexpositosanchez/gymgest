<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserType;
use App\Domain\User\ValueObjects\Password;
use App\Domain\User\ValueObjects\BirthDate;
use App\Domain\User\ValueObjects\Gender;
use App\Domain\User\ValueObjects\PersonName;
use App\Domain\User\ValueObjects\GymGoals;

class UserDomainService
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(
        string $email,
        string $password,
        string $userType,
        string $name,
        string $lastName,
        string $birthDate,
        string $gender,
        ?string $gymGoals = null
    ): UserEntity {
        $emailVO = new Email($email);

        if ($this->userRepository->existsByEmail($emailVO)) {
            throw new \DomainException('Ya existe una cuenta con este email');
        }

        $user = new UserEntity(
            UserId::generate(),
            $emailVO,
            new Password($password),
            new UserType($userType),
            new PersonName($name),
            new PersonName($lastName),
            new BirthDate($birthDate),
            new Gender($gender),
            new GymGoals($gymGoals)
        );

        return $this->userRepository->save($user);
    }

    public function isEmailUnique(Email $email): bool
    {
        return !$this->userRepository->existsByEmail($email);
    }

    /**
     * Validates minimum age (for backward compatibility with tests)
     * @deprecated Use BirthDate Value Object instead
     */
    public function validateMinimumAge($birthDate): bool
    {
        $age = now()->diffInYears($birthDate);
        return $age >= 16;
    }
}