<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        Mail::fake();
    }

    public function test_user_can_register_and_receives_verification_email()
    {
        $userData = [
            'email' => 'trainer@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'Test',
            'last_name' => 'Trainer',
            'birth_date' => '1990-01-01',
            'gender' => 'male'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201);
        Mail::assertSent(\App\Infrastructure\Mail\VerificationEmail::class);
    }

    public function test_user_cannot_login_without_email_verification()
    {
        $user = new UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'trainer@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'trainer';
        $user->name = 'Test';
        $user->last_name = 'Trainer';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = null; // Email no verificado
        $user->save();

        $credentials = [
            'email' => 'trainer@example.com',
            'password' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $credentials);

        $response->assertStatus(403)
                ->assertJson([
                    'error' => 'Debes verificar tu email antes de iniciar sesión'
                ]);
    }

    public function test_user_can_verify_email_with_valid_signed_url()
    {
        $user = new UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'trainer@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'trainer';
        $user->name = 'Test';
        $user->last_name = 'Trainer';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = null;
        $user->save();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email)
            ]
        );

        $response = $this->get($verificationUrl);

        // Ahora redirige al frontend en lugar de devolver JSON
        $response->assertStatus(302)
                ->assertRedirect(config('app.frontend_url') . '/verification-success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'trainer@example.com'
        ]);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_cannot_verify_email_with_invalid_signature()
    {
        $user = new UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'trainer@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'trainer';
        $user->name = 'Test';
        $user->last_name = 'Trainer';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = null;
        $user->save();

        // URL sin firma válida
        $invalidUrl = "/api/v1/auth/email/verify/{$user->id}/" . sha1($user->email) . "?expires=123&signature=invalid";

        $response = $this->get($invalidUrl);

        // Ahora redirige al frontend con el motivo del error
        $response->assertStatus(302)
                ->assertRedirect(config('app.frontend_url') . '/verification-failed?reason=invalid');
    }

    public function test_user_can_resend_verification_email()
    {
        $user = new UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'trainer@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'trainer';
        $user->name = 'Test';
        $user->last_name = 'Trainer';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = null;
        $user->save();

        $response = $this->postJson('/api/v1/auth/email/resend', [
            'email' => 'trainer@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Email de verificación reenviado'
                ]);

        Mail::assertSent(\App\Infrastructure\Mail\VerificationEmail::class);
    }

    public function test_verified_user_can_login()
    {
        $user = new UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'trainer@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'trainer';
        $user->name = 'Test';
        $user->last_name = 'Trainer';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = now(); // Email YA verificado
        $user->save();

        $credentials = [
            'email' => 'trainer@example.com',
            'password' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $credentials);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'user'
                ]);
    }
}
