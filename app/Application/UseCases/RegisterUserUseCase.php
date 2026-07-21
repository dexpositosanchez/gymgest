<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\RegisterUserDTO;
use App\Domain\Mail\Services\EmailServiceInterface;
use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Services\UserDomainService;

class RegisterUserUseCase
{
    /** @var UserDomainService */
    private $userDomainService;

    /** @var EmailServiceInterface */
    private $emailService;

    public function __construct(
        UserDomainService $userDomainService,
        EmailServiceInterface $emailService
    ) {
        $this->userDomainService = $userDomainService;
        $this->emailService = $emailService;
    }

    public function execute(RegisterUserDTO $dto): UserEntity
    {
        $user = $this->userDomainService->createUser(
            $dto->email,
            $dto->password,
            $dto->userType,
            $dto->name,
            $dto->lastName,
            $dto->birthDate,
            $dto->gender,
            $dto->gymGoals
        );

        // Send verification email
        $this->emailService->sendVerificationEmail(
            $user->getEmail(),
            $user->getId()
        );

        return $user;
    }
}