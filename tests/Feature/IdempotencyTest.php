<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class IdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        Mail::fake();
        Redis::flushall();
    }

    public function test_user_registration_with_same_idempotency_key_does_not_create_duplicate()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440100';

        $userData = [
            'email' => 'trainer@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'John',
            'last_name' => 'Doe',
            'birth_date' => '1990-01-01',
            'gender' => 'male'
        ];

        // First registration request
        $response1 = $this->postJson('/api/v1/auth/register', $userData, [
            'Idempotency-Key' => $uuid
        ]);

        $response1->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'trainer@example.com']);

        // Count users before second request
        $userCountBefore = UserEloquentModel::count();

        // Second registration request with same key
        $response2 = $this->postJson('/api/v1/auth/register', $userData, [
            'Idempotency-Key' => $uuid
        ]);

        $response2->assertStatus(201);
        $this->assertEquals('true', $response2->headers->get('X-Idempotent-Replayed'));

        // Verify no duplicate user was created
        $userCountAfter = UserEloquentModel::count();
        $this->assertEquals($userCountBefore, $userCountAfter);

        // Verify responses are identical
        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_user_registration_without_idempotency_key_processes_normally()
    {
        $userData = [
            'email' => 'trainer2@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'Jane',
            'last_name' => 'Smith',
            'birth_date' => '1985-05-15',
            'gender' => 'female'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'trainer2@example.com']);
        $this->assertNull($response->headers->get('X-Idempotent-Replayed'));
    }

    public function test_different_idempotency_keys_process_requests_independently()
    {
        $uuid1 = '550e8400-e29b-41d4-a716-446655440101';
        $uuid2 = '550e8400-e29b-41d4-a716-446655440102';

        $userData1 = [
            'email' => 'trainer3@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'User',
            'last_name' => 'One',
            'birth_date' => '1990-01-01',
            'gender' => 'male'
        ];

        $userData2 = [
            'email' => 'trainer4@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'User',
            'last_name' => 'Two',
            'birth_date' => '1991-02-02',
            'gender' => 'female'
        ];

        $response1 = $this->postJson('/api/v1/auth/register', $userData1, [
            'Idempotency-Key' => $uuid1
        ]);

        $response2 = $this->postJson('/api/v1/auth/register', $userData2, [
            'Idempotency-Key' => $uuid2
        ]);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'trainer3@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'trainer4@example.com']);
        $this->assertEquals(2, UserEloquentModel::count());
    }

    public function test_http_201_response_is_cached_and_replayed()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440103';

        $userData = [
            'email' => 'trainer5@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'Cache',
            'last_name' => 'Test',
            'birth_date' => '1992-03-03',
            'gender' => 'other'
        ];

        $response1 = $this->postJson('/api/v1/auth/register', $userData, [
            'Idempotency-Key' => $uuid
        ]);

        $response1->assertStatus(201);
        $userId1 = $response1->json('user.id');

        $response2 = $this->postJson('/api/v1/auth/register', $userData, [
            'Idempotency-Key' => $uuid
        ]);

        $response2->assertStatus(201);
        $this->assertEquals('true', $response2->headers->get('X-Idempotent-Replayed'));

        $userId2 = $response2->json('user.id');
        $this->assertEquals($userId1, $userId2);
    }

    public function test_http_422_validation_error_is_also_cached()
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440104';

        $invalidData = [
            'email' => 'invalid-email',  // Invalid email format
            'password' => 'weak',  // Weak password
            'user_type' => 'trainer',
            'name' => 'Test',
            'last_name' => 'User',
            'birth_date' => '1990-01-01',
            'gender' => 'male'
        ];

        $response1 = $this->postJson('/api/v1/auth/register', $invalidData, [
            'Idempotency-Key' => $uuid
        ]);

        $response1->assertStatus(422);
        $errors1 = $response1->json('errors');

        $response2 = $this->postJson('/api/v1/auth/register', $invalidData, [
            'Idempotency-Key' => $uuid
        ]);

        $response2->assertStatus(422);
        $this->assertEquals('true', $response2->headers->get('X-Idempotent-Replayed'));

        $errors2 = $response2->json('errors');
        $this->assertEquals($errors1, $errors2);
    }

    public function test_login_with_idempotency_key_works_correctly()
    {
        // Create a verified trainer user
        $user = new UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'trainer@login.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'trainer';
        $user->name = 'Login';
        $user->last_name = 'Test';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = now();
        $user->save();

        $uuid = '550e8400-e29b-41d4-a716-446655440105';

        $credentials = [
            'email' => 'trainer@login.com',
            'password' => 'Password123'
        ];

        // First login
        $response1 = $this->postJson('/api/v1/auth/login', $credentials, [
            'Idempotency-Key' => $uuid
        ]);

        $response1->assertStatus(200);
        $token1 = $response1->json('access_token');

        // Second login with same key
        $response2 = $this->postJson('/api/v1/auth/login', $credentials, [
            'Idempotency-Key' => $uuid
        ]);

        $response2->assertStatus(200);
        $this->assertEquals('true', $response2->headers->get('X-Idempotent-Replayed'));

        $token2 = $response2->json('access_token');
        $this->assertEquals($token1, $token2);
    }

    public function test_idempotency_with_invalid_uuid_format_returns_400()
    {
        $userData = [
            'email' => 'trainer6@example.com',
            'password' => 'Password123',
            'user_type' => 'trainer',
            'name' => 'Test',
            'last_name' => 'User',
            'birth_date' => '1990-01-01',
            'gender' => 'male'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData, [
            'Idempotency-Key' => 'not-a-valid-uuid'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Invalid Idempotency-Key format (must be UUIDv4)'
        ]);
    }

    public function test_get_request_ignores_idempotency_key()
    {
        // Create and authenticate a user
        $user = new UserEloquentModel();
        $user->id = \App\Domain\User\ValueObjects\UserId::generate()->getValue();
        $user->email = 'trainer@get.com';
        $user->password = Hash::make('Password123');
        $user->user_type = 'trainer';
        $user->name = 'Get';
        $user->last_name = 'Test';
        $user->birth_date = '1990-01-01';
        $user->gender = 'male';
        $user->gym_goals = null;
        $user->email_verified_at = now();
        $user->save();

        $token = auth('api')->login($user);

        $uuid = '550e8400-e29b-41d4-a716-446655440106';

        // GET request with idempotency key (should be ignored)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->withHeader('Idempotency-Key', $uuid)
                         ->getJson('/api/v1/auth/me');

        $response->assertStatus(200);

        // Verify key was not stored in Redis
        $this->assertNull(Redis::get("idempotency:{$uuid}"));
    }
}
