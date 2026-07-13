<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentOnlyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_authenticated_students(): void
    {
        // Arrange: Create student user
        $student = UserEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'student',
            'birth_date' => '1995-01-01',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);

        $token = auth()->login($student);

        // Act: Make authenticated request to student endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/students/me/routines');

        // Assert: Should NOT receive 403 Forbidden (may receive 200 or other valid response)
        $this->assertNotEquals(403, $response->status());
    }

    public function test_blocks_unauthenticated_users(): void
    {
        // Act: Make request without authentication
        $response = $this->getJson('/api/v1/students/me/routines');

        // Assert: Should receive 401 Unauthorized
        $response->assertStatus(401);
    }

    public function test_blocks_trainers(): void
    {
        // Arrange: Create trainer user
        $trainer = UserEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'name' => 'Test',
            'last_name' => 'Trainer',
            'email' => 'trainer@test.com',
            'password' => bcrypt('password'),
            'user_type' => 'trainer',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);

        $token = auth()->login($trainer);

        // Act: Make authenticated request as trainer
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/students/me/routines');

        // Assert: Should receive 403 Forbidden
        $response->assertStatus(403)
            ->assertJson(['error' => 'This endpoint is only for students']);
    }
}
