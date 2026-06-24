<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Infrastructure\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ResendVerificationEmailUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(string $email): void
    {
        $user = $this->userRepository->findByEmail(new Email($email));

        if (!$user) {
            throw new \DomainException('Usuario no encontrado');
        }

        if ($user->isEmailVerified()) {
            throw new \DomainException('El email ya está verificado');
        }

        // Generar URL firmada
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getId()->getValue(),
                'hash' => sha1($user->getEmail()->getValue())
            ]
        );

        // Enviar email
        Mail::to($email)->send(new VerificationEmail($verificationUrl));
    }
}
