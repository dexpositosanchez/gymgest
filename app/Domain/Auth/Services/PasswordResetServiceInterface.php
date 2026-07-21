<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\User\ValueObjects\Email;

interface PasswordResetServiceInterface
{
    /**
     * Generate and store a password reset token for an email
     *
     * @param Email $email
     * @return string The generated token
     */
    public function generateResetToken(Email $email): string;

    /**
     * Validate if a token is valid for the given email
     *
     * @param Email $email
     * @param string $token
     * @return bool True if token is valid, false otherwise
     */
    public function validateResetToken(Email $email, string $token): bool;

    /**
     * Invalidate/delete the reset token for an email
     *
     * @param Email $email
     * @return void
     */
    public function invalidateResetToken(Email $email): void;
}
