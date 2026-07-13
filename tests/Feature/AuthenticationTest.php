<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use App\Domain\User\ValueObjects\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->artisan('migrate');
    }

    public function test_user_can_register_as_student()
    {
        $userData = [
            'email' => 'student@example.com',
            'password' => 'Password123',
            'user_type' => 'student',
            'name' => 'John',
            'last_name' => 'Doe',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'gym_goals' => 'Lose weight and gain muscle'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'User registered successfully',
                    'user' => [
                        'email' => 'student@example.com',
                        'user_type' => 'student',
                        'name' => 'John',
                        'last_name' => 'Doe'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'student@example.com',
            'user_type' => 'student',
            'name' => 'John',
            'last_name' => 'Doe'
        ]);
    }

    public function test_user_can_register_as_trainer()
    {
        $userData = [
            'email' => 'trainer@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'Jane',
            'last_name' => 'Smith',
            'birth_date' => '1985-05-15',
            'gender' => 'female'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'User registered successfully',
                    'user' => [
                        'email' => 'trainer@example.com',
                        'user_type' => 'trainer',
                        'name' => 'Jane',
                        'last_name' => 'Smith'
                    ]
                ]);
    }

    public function test_registration_fails_with_invalid_email()
    {
        $userData = [
            'email' => 'invalid-email',
            'password' => 'Password123',
            'user_type' => 'student',
            'name' => 'John',
            'last_name' => 'Doe',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'gym_goals' => 'Get fit'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_weak_password()
    {
        $userData = [
            'email' => 'user@example.com',
            'password' => 'weak',
            'user_type' => 'student',
            'name' => 'John',
            'last_name' => 'Doe',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'gym_goals' => 'Get fit'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_when_student_has_no_gym_goals()
    {
        $userData = [
            'email' => 'student@example.com',
            'password' => 'Password123',
            'user_type' => 'student',
            'name' => 'John',
            'last_name' => 'Doe',
            'birth_date' => '1990-01-01',
            'gender' => 'male'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['gym_goals']);
    }

    public function test_registration_fails_for_underage_user()
    {
        $userData = [
            'email' => 'young@example.com',
            'password' => 'Password123',
            'user_type' => 'student',
            'name' => 'Young',
            'last_name' => 'User',
            'birth_date' => now()->subYears(15)->format('Y-m-d'),
            'gender' => 'male',
            'gym_goals' => 'Get fit'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['birth_date']);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        // Create a trainer user
        $user = new \App\Infrastructure\Persistence\Eloquent\UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'user@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'trainer';
        $user->name = 'John';
        $user->last_name = 'Doe';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = now(); // Email verificado
        $user->save();

        $credentials = [
            'email' => 'user@example.com',
            'password' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $credentials);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'email',
                        'user_type',
                        'name',
                        'last_name'
                    ]
                ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'WrongPassword123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $credentials);

        $response->assertStatus(401)
                ->assertJson([
                    'error' => 'Email o contraseña incorrectos'
                ]);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = new \App\Infrastructure\Persistence\Eloquent\UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'user@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'student';
        $user->name = 'John';
        $user->last_name = 'Doe';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = 'Get fit';
        $user->save();

        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
                ->assertJson([
                    'user' => [
                        'id' => $user->id,
                        'email' => 'user@example.com',
                        'user_type' => 'student',
                        'name' => 'John',
                        'last_name' => 'Doe'
                    ]
                ]);
    }

    public function test_unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout()
    {
        $user = new \App\Infrastructure\Persistence\Eloquent\UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'user@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'student';
        $user->name = 'John';
        $user->last_name = 'Doe';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = 'Get fit';
        $user->save();

        $token = auth('api')->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Successfully logged out'
                ]);
    }

    // Edge cases and boundary tests
    public function test_registration_fails_with_duplicate_email()
    {
        // Create first user
        $user = new \App\Infrastructure\Persistence\Eloquent\UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'existing@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'student';
        $user->name = 'First';
        $user->last_name = 'User';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = 'Get fit';
        $user->save();

        // Try to register with same email
        $userData = [
            'email' => 'existing@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'Second',
            'last_name' => 'User',
            'birth_date' => '1985-01-01',
            'gender' => 'female'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_with_minimum_age_boundary()
    {
        $userData = [
            'email' => 'boundary@example.com',
            'password' => 'Password123',
            'user_type' => 'student',
            'name' => 'Boundary',
            'last_name' => 'Case',
            'birth_date' => now()->subYears(16)->subDay()->format('Y-m-d'),
            'gender' => 'other',
            'gym_goals' => 'Test boundary case'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201);
    }

    public function test_student_user_can_login()
    {
        // Create a verified student user
        $user = new \App\Infrastructure\Persistence\Eloquent\UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'student@example.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'student';
        $user->name = 'Student';
        $user->last_name = 'User';
        $user->birth_date = '1995-01-01';
        $user->gender = 'male';
        $user->gym_goals = 'Get fit';
        $user->email_verified_at = now(); // Email verified
        $user->save();

        // Attempt login with valid credentials
        $credentials = [
            'email' => 'student@example.com',
            'password' => 'Password123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $credentials);

        // Students can now login
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'access_token',
                    'token_type',
                    'user' => ['id', 'email', 'user_type', 'name', 'last_name']
                ]);

        // Verify user_type is student
        $this->assertEquals('student', $response->json('user.user_type'));
    }
}