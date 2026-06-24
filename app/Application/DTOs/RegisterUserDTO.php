<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class RegisterUserDTO
{
    /** @var string */
    public $email;
    /** @var string */
    public $password;
    /** @var string */
    public $userType;
    /** @var string */
    public $name;
    /** @var string */
    public $lastName;
    /** @var string */
    public $birthDate;
    /** @var string */
    public $gender;
    /** @var string|null */
    public $gymGoals;

    public function __construct(
        string $email,
        string $password,
        string $userType,
        string $name,
        string $lastName,
        string $birthDate,
        string $gender,
        ?string $gymGoals = null
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->userType = $userType;
        $this->name = $name;
        $this->lastName = $lastName;
        $this->birthDate = $birthDate;
        $this->gender = $gender;
        $this->gymGoals = $gymGoals;
    }
}