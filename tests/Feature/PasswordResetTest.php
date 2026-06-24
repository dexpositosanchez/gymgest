<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        Mail::fake();
    }

    public function test_user_can_request_password_reset()
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
        $user->email_verified_at = now();
        $user->save();

        $response = $this->postJson('/api/v1/auth/password/email', [
            'email' => 'trainer@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Te hemos enviado un email con instrucciones para restablecer tu contraseña'
                ]);

        $this->assertDatabaseHas('password_resets', [
            'email' => 'trainer@example.com'
        ]);

        Mail::assertSent(\App\Infrastructure\Mail\PasswordResetEmail::class);
    }

    public function test_password_reset_returns_generic_message_for_nonexistent_user()
    {
        // Por seguridad, no revelar si el usuario existe o no
        $response = $this->postJson('/api/v1/auth/password/email', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Te hemos enviado un email con instrucciones para restablecer tu contraseña'
                ]);
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        $user = new UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'trainer@example.com';
        $user->password = Hash::make('OldPassword123');
        $user->user_type = 'trainer';
        $user->name = 'Test';
        $user->last_name = 'Trainer';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = now();
        $user->save();

        // Generar token manualmente
        $token = \Illuminate\Support\Str::random(60);
        DB::table('password_resets')->insert([
            'email' => 'trainer@example.com',
            'token' => Hash::make($token),
            'created_at' => now()
        ]);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'trainer@example.com',
            'token' => $token,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Contraseña restablecida correctamente'
                ]);

        // Verificar que el token fue eliminado
        $this->assertDatabaseMissing('password_resets', [
            'email' => 'trainer@example.com'
        ]);

        // Verificar que se puede hacer login con la nueva contraseña
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
    }

    public function test_user_cannot_reset_password_with_invalid_token()
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
        $user->email_verified_at = now();
        $user->save();

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'trainer@example.com',
            'token' => 'invalid-token',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Token inválido o expirado'
                ]);
    }

    public function test_user_cannot_reset_password_with_expired_token()
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
        $user->email_verified_at = now();
        $user->save();

        // Token expirado (creado hace más de 60 minutos)
        $token = \Illuminate\Support\Str::random(60);
        DB::table('password_resets')->insert([
            'email' => 'trainer@example.com',
            'token' => Hash::make($token),
            'created_at' => now()->subMinutes(61)
        ]);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'trainer@example.com',
            'token' => $token,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'error' => 'Token inválido o expirado'
                ]);
    }

    public function test_password_reset_requires_password_confirmation()
    {
        $response = $this->postJson('/api/v1/auth/password/reset', [
            'email' => 'trainer@example.com',
            'token' => 'some-token',
            'password' => 'NewPassword123',
            'password_confirmation' => 'DifferentPassword123'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }
}
