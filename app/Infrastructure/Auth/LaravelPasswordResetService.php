<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Services\PasswordResetServiceInterface;
use App\Domain\User\ValueObjects\Email;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LaravelPasswordResetService implements PasswordResetServiceInterface
{
    /**
     * Token expiration time in minutes
     */
    private const TOKEN_EXPIRATION_MINUTES = 60;

    public function generateResetToken(Email $email): string
    {
        // Generate random token
        $token = Str::random(60);

        // Delete any existing tokens for this email
        DB::table('password_resets')
            ->where('email', $email->getValue())
            ->delete();

        // Insert new token
        DB::table('password_resets')->insert([
            'email' => $email->getValue(),
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        return $token;
    }

    public function validateResetToken(Email $email, string $token): bool
    {
        $resetRecord = DB::table('password_resets')
            ->where('email', $email->getValue())
            ->first();

        if ($resetRecord === null) {
            return false;
        }

        // Check if token is expired
        $createdAt = new \DateTime($resetRecord->created_at);
        $now = new \DateTime();
        $diffInMinutes = ($now->getTimestamp() - $createdAt->getTimestamp()) / 60;

        if ($diffInMinutes > self::TOKEN_EXPIRATION_MINUTES) {
            return false;
        }

        // Verify token hash
        return Hash::check($token, $resetRecord->token);
    }

    public function invalidateResetToken(Email $email): void
    {
        DB::table('password_resets')
            ->where('email', $email->getValue())
            ->delete();
    }
}
