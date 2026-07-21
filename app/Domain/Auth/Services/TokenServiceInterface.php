<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\User\ValueObjects\UserId;

interface TokenServiceInterface
{
    /**
     * Generate JWT token for a user
     *
     * @param UserId $userId
     * @return string JWT token
     */
    public function generateTokenForUser(UserId $userId): string;

    /**
     * Invalidate current JWT token
     *
     * @return void
     */
    public function invalidateCurrentToken(): void;
}
