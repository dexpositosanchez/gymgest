<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use App\Domain\Mail\Services\EmailServiceInterface;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\UserId;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\URL;

class ResendEmailService implements EmailServiceInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.resend.com',
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.resend.api_key'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function sendVerificationEmail(Email $to, UserId $userId): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $userId->getValue(),
                'hash' => sha1($to->getValue())
            ]
        );

        $parsedUrl = parse_url($signedUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $frontendUrl = config('app.frontend_url')
            . '/email/verify/'
            . $userId->getValue()
            . '/' . sha1($to->getValue())
            . '?' . http_build_query($queryParams);

        $this->sendEmail(
            $to->getValue(),
            'Verifica tu cuenta en GymGest',
            $this->verificationTemplate($frontendUrl)
        );
    }

    public function sendPasswordResetEmail(Email $to, string $resetUrl): void
    {
        $this->sendEmail(
            $to->getValue(),
            'Recupera tu contraseña en GymGest',
            $this->passwordResetTemplate($resetUrl)
        );
    }

    private function sendEmail(string $to, string $subject, string $html): void
    {
        $this->client->post('/emails', [
            'json' => [
                'from' => config('mail.from.name') . ' <' . config('mail.from.address') . '>',
                'to' => [$to],
                'subject' => $subject,
                'html' => $html,
            ],
        ]);
    }

    private function verificationTemplate(string $url): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>Verifica tu cuenta en GymGest</h2>
            <p>Haz clic en el botón para verificar tu cuenta:</p>
            <a href="' . $url . '" style="background: #3B82F6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                Verificar cuenta
            </a>
            <p>Este enlace expira en 60 minutos.</p>
        </div>';
    }

    private function passwordResetTemplate(string $url): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>Recupera tu contraseña en GymGest</h2>
            <p>Haz clic en el botón para resetear tu contraseña:</p>
            <a href="' . $url . '" style="background: #3B82F6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                Resetear contraseña
            </a>
            <p>Este enlace expira en 60 minutos.</p>
        </div>';
    }
}