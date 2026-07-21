<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Mail\Services\EmailServiceInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;

class ResendVerificationEmailUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var EmailServiceInterface */
    private $emailService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        EmailServiceInterface $emailService
    ) {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    public function execute(string $email): void
    {
        $emailVO = new Email($email);
        $user = $this->userRepository->findByEmail($emailVO);

        if (!$user) {
            throw new \DomainException('Usuario no encontrado');
        }

        if ($user->isEmailVerified()) {
            throw new \DomainException('El email ya está verificado');
        }

        // Send verification email
        $this->emailService->sendVerificationEmail($emailVO, $user->getId());
    }
}
