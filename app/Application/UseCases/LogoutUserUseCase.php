<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Auth\Services\TokenServiceInterface;

class LogoutUserUseCase
{
    /** @var TokenServiceInterface */
    private $tokenService;

    public function __construct(TokenServiceInterface $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function execute(): void
    {
        $this->tokenService->invalidateCurrentToken();
    }
}