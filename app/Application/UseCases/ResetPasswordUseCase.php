<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\Password;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordUseCase
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(string $email, string $token, string $newPassword): void
    {
        // Buscar registro en password_resets
        $resetRecord = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            throw new \DomainException('Token inválido o expirado');
        }

        // Validar token
        if (!Hash::check($token, $resetRecord->token)) {
            throw new \DomainException('Token inválido o expirado');
        }

        // Validar expiración (60 minutos)
        $expirationMinutes = (int) env('RESET_PASSWORD_EXPIRE_MINUTES', 60);
        $createdAt = new \DateTime($resetRecord->created_at);
        $now = new \DateTime();
        $diffMinutes = ($now->getTimestamp() - $createdAt->getTimestamp()) / 60;

        if ($diffMinutes > $expirationMinutes) {
            // Eliminar token expirado
            DB::table('password_resets')->where('email', $email)->delete();
            throw new \DomainException('Token inválido o expirado');
        }

        // Buscar usuario
        $user = $this->userRepository->findByEmail(new Email($email));

        if (!$user) {
            throw new \DomainException('Usuario no encontrado');
        }

        // Actualizar contraseña
        $eloquentModel = UserEloquentModel::find($user->getId()->getValue());
        $eloquentModel->password = (new Password($newPassword))->getHashedValue();
        $eloquentModel->save();

        // Eliminar token usado
        DB::table('password_resets')->where('email', $email)->delete();
    }
}
