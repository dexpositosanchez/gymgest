<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Services\TokenServiceInterface;
use App\Domain\User\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTTokenService implements TokenServiceInterface
{
    public function generateTokenForUser(UserId $userId): string
    {
        // JWTAuth requires an Eloquent model, so we load it here
        // This keeps the Infrastructure dependency isolated from Application layer
        $eloquentModel = UserEloquentModel::find($userId->getValue());

        if ($eloquentModel === null) {
            throw new \DomainException('User not found');
        }

        return JWTAuth::fromUser($eloquentModel);
    }

    public function invalidateCurrentToken(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
