<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\RegisterUserDTO;
use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Services\UserDomainService;
use App\Infrastructure\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RegisterUserUseCase
{
    /** @var UserDomainService */
    private $userDomainService;

    public function __construct(UserDomainService $userDomainService)
    {
        $this->userDomainService = $userDomainService;
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

        // Generar signed URL del backend (para validar firma)
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getId()->getValue(),
                'hash' => sha1($user->getEmail()->getValue())
            ]
        );

        // Extraer query params de la signed URL
        $parsedUrl = parse_url($signedUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Construir enlace al frontend con los mismos params
        $frontendUrl = config('app.frontend_url')
            . '/email/verify/'
            . $user->getId()->getValue()
            . '/' . sha1($user->getEmail()->getValue())
            . '?' . http_build_query($queryParams);

        // Enviar email de verificación
        Mail::to($dto->email)->send(new VerificationEmail($frontendUrl));

        return $user;
    }
}