<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutUserUseCase
{
    public function execute(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}