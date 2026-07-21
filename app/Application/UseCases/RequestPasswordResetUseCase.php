<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Auth\Services\PasswordResetServiceInterface;
use App\Domain\Mail\Services\EmailServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;

class RequestPasswordResetUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var PasswordResetServiceInterface */
    private $passwordResetService;

    /** @var EmailServiceInterface */
    private $emailService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        PasswordResetServiceInterface $passwordResetService,
        EmailServiceInterface $emailService
    ) {
        $this->userRepository = $userRepository;
        $this->passwordResetService = $passwordResetService;
        $this->emailService = $emailService;
    }

    public function execute(string $email): void
    {
        $emailVO = new Email($email);
        $user = $this->userRepository->findByEmail($emailVO);

        if (!$user) {
            throw new \DomainException('Usuario no encontrado');
        }

        // Generar token de restablecimiento
        $token = $this->passwordResetService->generateResetToken($emailVO);

        // Construir URL de restablecimiento para frontend
        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($email);

        // Enviar email
        $this->emailService->sendPasswordResetEmail($emailVO, $resetUrl);
    }
}
