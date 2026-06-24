<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\LoginUserDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginUserUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(LoginUserDTO $dto): array
    {
        $email = new Email($dto->email);
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($dto->password)) {
            throw new \InvalidArgumentException('Email o contraseña incorrectos');
        }

        // Verificar que el usuario es trainer
        if (!$user->getUserType()->isTrainer()) {
            throw new \DomainException('Esta aplicación es solo para entrenadores');
        }

        // Verificar que el email está verificado
        if (!$user->isEmailVerified()) {
            throw new \DomainException('Debes verificar tu email antes de iniciar sesión');
        }

        $eloquentModel = UserEloquentModel::find($user->getId()->getValue());
        $token = JWTAuth::fromUser($eloquentModel);

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