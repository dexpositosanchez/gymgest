<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Auth\Services\PasswordResetServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Password;

class ResetPasswordUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var PasswordResetServiceInterface */
    private $passwordResetService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordResetServiceInterface $passwordResetService
    ) {
        $this->userRepository = $userRepository;
        $this->passwordResetService = $passwordResetService;
    }

    public function execute(string $email, string $token, string $newPassword): void
    {
        $emailVO = new Email($email);

        // Validar token de restablecimiento
        if (!$this->passwordResetService->validateResetToken($emailVO, $token)) {
            throw new \DomainException('Token inválido o expirado');
        }

        // Buscar usuario
        $user = $this->userRepository->findByEmail($emailVO);

        if (!$user) {
            throw new \DomainException('Usuario no encontrado');
        }

        // Actualizar contraseña
        $newPasswordVO = new Password($newPassword);
        $this->userRepository->updatePassword($user->getId(), $newPasswordVO);

        // Invalidar token usado
        $this->passwordResetService->invalidateResetToken($emailVO);
    }
}
