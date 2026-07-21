<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use App\Domain\Mail\Services\EmailServiceInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class LaravelEmailService implements EmailServiceInterface
{
    public function sendVerificationEmail(Email $to, UserId $userId): void
    {
        // Generate signed URL from backend (to validate signature)
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $userId->getValue(),
                'hash' => sha1($to->getValue())
            ]
        );

        // Extract query params from signed URL
        $parsedUrl = parse_url($signedUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Build frontend link with same params
        $frontendUrl = config('app.frontend_url')
            . '/email/verify/'
            . $userId->getValue()
            . '/' . sha1($to->getValue())
            . '?' . http_build_query($queryParams);

        // Send verification email
        Mail::to($to->getValue())->send(new VerificationEmail($frontendUrl));
    }

    public function sendPasswordResetEmail(Email $to, string $resetUrl): void
    {
        Mail::to($to->getValue())->send(new PasswordResetEmail($resetUrl));
    }
}
