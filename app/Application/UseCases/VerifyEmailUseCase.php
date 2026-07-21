<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\UserId;

class VerifyEmailUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(string $userId, string $hash): void
    {
        $userIdVO = new UserId($userId);
        $user = $this->userRepository->findById($userIdVO);

        if (!$user) {
            throw new \DomainException('Usuario no encontrado');
        }

        // Verificar hash del email
        if (!hash_equals($hash, sha1($user->getEmail()->getValue()))) {
            throw new \DomainException('Enlace de verificación inválido');
        }

        // Si ya está verificado, no hacer nada
        if ($user->isEmailVerified()) {
            return;
        }

        // Mark email as verified using repository
        $this->userRepository->markEmailAsVerified($userIdVO);
    }
}
