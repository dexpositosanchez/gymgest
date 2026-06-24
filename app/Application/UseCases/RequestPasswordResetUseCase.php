<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Infrastructure\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RequestPasswordResetUseCase
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

        // Generar token aleatorio
        $token = Str::random(60);

        // Eliminar tokens antiguos del mismo email
        DB::table('password_resets')->where('email', $email)->delete();

        // Insertar nuevo token
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        // Generar URL de reset (frontend)
        $resetUrl = env('FRONTEND_URL', 'http://localhost:3000') . '/reset-password?token=' . $token . '&email=' . urlencode($email);

        // Enviar email
        Mail::to($email)->send(new PasswordResetEmail($resetUrl));
    }
}
