<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\LoginUserDTO;
use App\Domain\Auth\Services\TokenServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;

class LoginUserUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var TokenServiceInterface */
    private $tokenService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenServiceInterface $tokenService
    ) {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
    }

    public function execute(LoginUserDTO $dto): array
    {
        $email = new Email($dto->email);
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($dto->password)) {
            throw new \InvalidArgumentException('Email o contraseña incorrectos');
        }

        // Verificar que el email está verificado
        if (!$user->isEmailVerified()) {
            throw new \DomainException('Debes verificar tu email antes de iniciar sesión');
        }

        $token = $this->tokenService->generateTokenForUser($user->getId());

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => [
                'id' => $user->getId()->getValue(),
                'email' => $user->getEmail()->getValue(),
                'user_type' => $user->getUserType()->getValue(),
                'name' => $user->getName()->getValue(),
                'last_name' => $user->getLastName()->getValue()
            ]
        ];
    }
}