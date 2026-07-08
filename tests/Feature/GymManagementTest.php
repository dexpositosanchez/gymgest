<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GymManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $trainerId;
    private string $otherTrainerId;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create trainers
        $this->trainerId = Str::uuid()->toString();
        $trainer = UserEloquentModel::create([
            'id' => $this->trainerId,
            'name' => 'Test Trainer',
            'last_name' => 'User',
            'email' => 'trainer@test.com',
            'password' => bcrypt('Password123!'),
            'user_type' => 'trainer',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);

        $this->otherTrainerId = Str::uuid()->toString();
        UserEloquentModel::create([
            'id' => $this->otherTrainerId,
            'name' => 'Other Trainer',
            'last_name' => 'User',
            'email' => 'other@test.com',
            'password' => bcrypt('Password123!'),
            'user_type' => 'trainer',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);

        // Generate JWT token for main trainer
        $this->token = auth()->login($trainer);
    }

    public function test_trainer_can_create_gym(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'address',
                    'locality',
                    'province',
                    'country',
                    'is_active',
                    'trainer_id',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'FitZone Madrid Centro',
                    'address' => 'Calle Gran Vía, 123',
                    'locality' => 'Madrid',
                    'province' => 'Comunidad de Madrid',
                    'country' => 'España',
                    'is_active' => true,
                    'trainer_id' => $this->trainerId,
                ],
            ]);
    }

    public function test_trainer_can_list_own_gyms(): void
    {
        // Create gyms for trainer
        GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Norte',
            'address' => 'Calle Serrano, 456',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        // Create gym for other trainer (should not appear)
        GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->otherTrainerId,
            'name' => 'Other Trainer Gym',
            'address' => 'Calle Alcalá, 789',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/gyms');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'address',
                        'locality',
                        'province',
                        'country',
                        'is_active',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_trainer_can_get_gym_details(): void
    {
        $gymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $gymId,
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/gyms/{$gymId}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $gymId,
                    'name' => 'FitZone Madrid Centro',
                    'address' => 'Calle Gran Vía, 123',
                    'locality' => 'Madrid',
                    'province' => 'Comunidad de Madrid',
                    'country' => 'España',
                    'is_active' => true,
                ],
            ]);
    }

    public function test_trainer_can_update_own_gym(): void
    {
        $gymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $gymId,
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        $updateData = [
            'name' => 'FitZone Madrid Centro Updated',
            'address' => 'Calle Serrano, 456',
            'locality' => 'Barcelona',
            'province' => 'Cataluña',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$gymId}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $gymId,
                    'name' => 'FitZone Madrid Centro Updated',
                    'address' => 'Calle Serrano, 456',
                    'locality' => 'Barcelona',
                    'province' => 'Cataluña',
                    'country' => 'España',
                ],
            ]);
    }

    public function test_trainer_cannot_update_other_trainers_gym(): void
    {
        $gymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $gymId,
            'trainer_id' => $this->otherTrainerId,
            'name' => 'Other Trainer Gym',
            'address' => 'Calle Alcalá, 789',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        $updateData = [
            'name' => 'Hacked Gym Name',
            'address' => 'Calle Serrano, 456',
            'locality' => 'Barcelona',
            'province' => 'Cataluña',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$gymId}", $updateData);

        $response->assertStatus(403);
    }

    public function test_trainer_can_delete_own_gym(): void
    {
        $gymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $gymId,
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/gyms/{$gymId}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('gyms', ['id' => $gymId]);
    }

    public function test_trainer_cannot_delete_other_trainers_gym(): void
    {
        $gymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $gymId,
            'trainer_id' => $this->otherTrainerId,
            'name' => 'Other Trainer Gym',
            'address' => 'Calle Alcalá, 789',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/gyms/{$gymId}");

        $response->assertStatus(403);
    }

    public function test_trainer_can_toggle_gym_to_inactive(): void
    {
        $gymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $gymId,
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$gymId}/toggle");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_active' => false,
                ],
            ]);
    }

    public function test_trainer_can_toggle_gym_to_active(): void
    {
        $gymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $gymId,
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$gymId}/toggle");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'is_active' => true,
                ],
            ]);
    }

    public function test_inactive_gyms_not_listed_by_default(): void
    {
        // Active gym
        GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        // Inactive gym
        GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Norte (Inactive)',
            'address' => 'Calle Serrano, 456',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/gyms');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_inactive_gyms_listed_with_include_inactive_param(): void
    {
        // Active gym
        GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);

        // Inactive gym
        GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'name' => 'FitZone Madrid Norte (Inactive)',
            'address' => 'Calle Serrano, 456',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/gyms?include_inactive=true');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_create_gym_requires_name(): void
    {
        $gymData = [
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_gym_requires_address(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['address']);
    }

    public function test_create_gym_requires_locality(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['locality']);
    }

    public function test_create_gym_requires_province(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['province']);
    }

    public function test_create_gym_requires_country(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    }

    public function test_name_cannot_exceed_255_characters(): void
    {
        $gymData = [
            'name' => str_repeat('a', 256),
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_address_cannot_exceed_255_characters(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'address' => str_repeat('a', 256),
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['address']);
    }

    public function test_locality_cannot_exceed_100_characters(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => str_repeat('a', 101),
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['locality']);
    }

    public function test_province_cannot_exceed_100_characters(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => str_repeat('a', 101),
            'country' => 'España',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['province']);
    }

    public function test_country_cannot_exceed_100_characters(): void
    {
        $gymData = [
            'name' => 'FitZone Madrid Centro',
            'address' => 'Calle Gran Vía, 123',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => str_repeat('a', 101),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/gyms', $gymData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    }
}
