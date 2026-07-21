<?php

declare(strict_types=1);

namespace App\Domain\Mail\Services;

use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;

interface EmailServiceInterface
{
    /**
     * Send email verification to a user
     *
     * @param Email $to
     * @param UserId $userId
     * @return void
     */
    public function sendVerificationEmail(Email $to, UserId $userId): void;

    /**
     * Send password reset email with reset URL
     *
     * @param Email $to
     * @param string $resetUrl
     * @return void
     */
    public function sendPasswordResetEmail(Email $to, string $resetUrl): void;
}
