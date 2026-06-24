<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use App\Domain\User\ValueObjects\UserType;
use App\Domain\User\ValueObjects\Password;
use App\Domain\User\ValueObjects\BirthDate;
use App\Domain\User\ValueObjects\Gender;
use App\Domain\User\ValueObjects\PersonName;
use App\Domain\User\ValueObjects\GymGoals;
use App\Domain\User\ValueObjects\EmailVerifiedAt;

class UserEntity
{
    /** @var UserId */
    private $id;
    /** @var Email */
    private $email;
    /** @var Password */
    private $password;
    /** @var UserType */
    private $userType;
    /** @var PersonName */
    private $name;
    /** @var PersonName */
    private $lastName;
    /** @var BirthDate */
    private $birthDate;
    /** @var Gender */
    private $gender;
    /** @var GymGoals */
    private $gymGoals;
    /** @var EmailVerifiedAt */
    private $emailVerifiedAt;

    public function __construct(
        UserId $id,
        Email $email,
        Password $password,
        UserType $userType,
        PersonName $name,
        PersonName $lastName,
        BirthDate $birthDate,
        Gender $gender,
        GymGoals $gymGoals,
        EmailVerifiedAt $emailVerifiedAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->userType = $userType;
        $this->name = $name;
        $this->lastName = $lastName;
        $this->birthDate = $birthDate;
        $this->gender = $gender;
        $this->gymGoals = $gymGoals;
        $this->emailVerifiedAt = $emailVerifiedAt ?? new EmailVerifiedAt(null);

        $this->validate();
    }

    private function validate(): void
    {
        if ($this->userType->isStudent() && !$this->gymGoals->hasValue()) {
            throw new \DomainException('Students must provide gym goals');
        }
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getUserType(): UserType
    {
        return $this->userType;
    }

    public function getName(): PersonName
    {
        return $this->name;
    }

    public function getLastName(): PersonName
    {
        return $this->lastName;
    }

    public function getBirthDate(): BirthDate
    {
        return $this->birthDate;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function getGymGoals(): GymGoals
    {
        return $this->gymGoals;
    }

    public function isTrainer(): bool
    {
        return $this->userType->isTrainer();
    }

    public function isStudent(): bool
    {
        return $this->userType->isStudent();
    }

    public function getFullName(): string
    {
        return sprintf('%s %s', $this->name->getValue(), $this->lastName->getValue());
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return $this->password->verify($plainPassword);
    }

    public function getEmailVerifiedAt(): EmailVerifiedAt
    {
        return $this->emailVerifiedAt;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt->isVerified();
    }

    public function markEmailAsVerified(): void
    {
        $this->emailVerifiedAt = EmailVerifiedAt::now();
    }
}
