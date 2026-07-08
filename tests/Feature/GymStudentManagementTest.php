<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class GymStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $trainerId;
    private string $otherTrainerId;
    private string $studentId;
    private string $anotherStudentId;
    private string $token;
    private string $gymId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create trainer user
        $this->trainerId = Str::uuid()->toString();
        $trainer = UserEloquentModel::create([
            'id' => $this->trainerId,
            'email' => 'trainer@test.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Test',
            'last_name' => 'Trainer',
            'user_type' => 'trainer',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);

        $this->token = auth()->login($trainer);

        // Create another trainer
        $this->otherTrainerId = Str::uuid()->toString();
        UserEloquentModel::create([
            'id' => $this->otherTrainerId,
            'email' => 'othertrainer@test.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Other',
            'last_name' => 'Trainer',
            'user_type' => 'trainer',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);

        // Create student users
        $this->studentId = Str::uuid()->toString();
        UserEloquentModel::create([
            'id' => $this->studentId,
            'email' => 'student@test.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Test',
            'last_name' => 'Student',
            'user_type' => 'student',
            'birth_date' => '2000-01-01',
            'gender' => 'male',
            'gym_goals' => 'Build muscle',
            'email_verified_at' => now(),
        ]);

        $this->anotherStudentId = Str::uuid()->toString();
        UserEloquentModel::create([
            'id' => $this->anotherStudentId,
            'email' => 'anotherstudent@test.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Another',
            'last_name' => 'Student',
            'user_type' => 'student',
            'birth_date' => '1998-01-01',
            'gender' => 'female',
            'gym_goals' => 'Lose weight',
            'email_verified_at' => now(),
        ]);

        // Create gym
        $this->gymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $this->gymId,
            'trainer_id' => $this->trainerId,
            'name' => 'Test Gym',
            'address' => 'Test Address',
            'locality' => 'Madrid',
            'province' => 'Comunidad de Madrid',
            'country' => 'España',
            'is_active' => true,
        ]);
    }

    public function test_trainer_can_enroll_student_with_valid_email(): void
    {
        $enrollData = [
            'email' => 'student@test.com',
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'gymId',
                    'studentId',
                    'studentName',
                    'studentEmail',
                    'quotaExpiresAt',
                    'isActive',
                    'quotaStatus',
                ],
            ])
            ->assertJson([
                'data' => [
                    'gymId' => $this->gymId,
                    'studentEmail' => 'student@test.com',
                    'isActive' => true,
                    'quotaStatus' => 'active',
                ],
            ]);

        $this->assertDatabaseHas('gym_students', [
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'is_active' => true,
        ]);
    }

    public function test_enroll_fails_when_email_does_not_exist(): void
    {
        $enrollData = [
            'email' => 'nonexistent@test.com',
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'No existe ningún alumno registrado con ese email',
            ]);
    }

    public function test_enroll_fails_when_user_is_not_student(): void
    {
        $enrollData = [
            'email' => 'othertrainer@test.com',
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Este usuario no es un alumno',
            ]);
    }

    public function test_enroll_fails_when_student_already_active_in_gym(): void
    {
        // First enrollment
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
            'is_active' => true,
        ]);

        // Try to enroll again
        $enrollData = [
            'email' => 'student@test.com',
            'quota_expires_at' => date('Y-m-d', strtotime('+60 days')),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Este alumno ya está matriculado en este gimnasio',
            ]);
    }

    public function test_enroll_reactivates_inactive_student(): void
    {
        // Create inactive enrollment
        $existingId = Str::uuid()->toString();
        GymStudentEloquentModel::create([
            'id' => $existingId,
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('-10 days')),
            'is_active' => false,
        ]);

        $newQuotaDate = date('Y-m-d', strtotime('+30 days'));
        $enrollData = [
            'email' => 'student@test.com',
            'quota_expires_at' => $newQuotaDate,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'isActive' => true,
                    'quotaExpiresAt' => $newQuotaDate,
                ],
            ]);

        $this->assertDatabaseHas('gym_students', [
            'id' => $existingId,
            'is_active' => true,
            'quota_expires_at' => $newQuotaDate,
        ]);

        // Ensure no duplicate was created
        $this->assertDatabaseCount('gym_students', 1);
    }

    public function test_enroll_fails_with_past_date(): void
    {
        $enrollData = [
            'email' => 'student@test.com',
            'quota_expires_at' => date('Y-m-d', strtotime('-1 day')),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quota_expires_at']);
    }

    public function test_enroll_fails_with_today_date(): void
    {
        $enrollData = [
            'email' => 'student@test.com',
            'quota_expires_at' => date('Y-m-d'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quota_expires_at']);
    }

    public function test_trainer_can_list_gym_students(): void
    {
        // Create active student
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
            'is_active' => true,
        ]);

        // Create another active student
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->anotherStudentId,
            'quota_expires_at' => date('Y-m-d', strtotime('-5 days')),
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/gyms/{$this->gymId}/students");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'gymId',
                        'studentId',
                        'studentName',
                        'studentEmail',
                        'quotaExpiresAt',
                        'isActive',
                        'quotaStatus',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_trainer_cannot_list_students_from_other_trainer_gym(): void
    {
        $otherGymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $otherGymId,
            'trainer_id' => $this->otherTrainerId,
            'name' => 'Other Gym',
            'address' => 'Other Address',
            'locality' => 'Barcelona',
            'province' => 'Cataluña',
            'country' => 'España',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/gyms/{$otherGymId}/students");

        $response->assertStatus(403);
    }

    public function test_trainer_can_update_student_quota(): void
    {
        $gymStudentId = Str::uuid()->toString();
        GymStudentEloquentModel::create([
            'id' => $gymStudentId,
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+10 days')),
            'is_active' => true,
        ]);

        $newQuotaDate = date('Y-m-d', strtotime('+60 days'));
        $updateData = [
            'quota_expires_at' => $newQuotaDate,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'quotaExpiresAt' => $newQuotaDate,
                    'isActive' => true,
                ],
            ]);

        $this->assertDatabaseHas('gym_students', [
            'id' => $gymStudentId,
            'quota_expires_at' => $newQuotaDate,
        ]);
    }

    public function test_trainer_can_deactivate_student(): void
    {
        $gymStudentId = Str::uuid()->toString();
        GymStudentEloquentModel::create([
            'id' => $gymStudentId,
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}");

        $response->assertStatus(204);

        $this->assertDatabaseHas('gym_students', [
            'id' => $gymStudentId,
            'is_active' => false,
        ]);
    }

    public function test_trainer_can_reactivate_student(): void
    {
        $gymStudentId = Str::uuid()->toString();
        GymStudentEloquentModel::create([
            'id' => $gymStudentId,
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('-10 days')),
            'is_active' => false,
        ]);

        $newQuotaDate = date('Y-m-d', strtotime('+30 days'));
        $reactivateData = [
            'quota_expires_at' => $newQuotaDate,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/reactivate", $reactivateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'isActive' => true,
                    'quotaExpiresAt' => $newQuotaDate,
                ],
            ]);

        $this->assertDatabaseHas('gym_students', [
            'id' => $gymStudentId,
            'is_active' => true,
            'quota_expires_at' => $newQuotaDate,
        ]);
    }

    public function test_trainer_can_list_all_students_from_all_gyms(): void
    {
        // Create another gym for the trainer
        $anotherGymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $anotherGymId,
            'trainer_id' => $this->trainerId,
            'name' => 'Another Gym',
            'address' => 'Another Address',
            'locality' => 'Valencia',
            'province' => 'Comunitat Valenciana',
            'country' => 'España',
            'is_active' => true,
        ]);

        // Enroll students in first gym
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
            'is_active' => true,
        ]);

        // Enroll students in second gym
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $anotherGymId,
            'student_id' => $this->anotherStudentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+20 days')),
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/students');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'gymId',
                        'studentId',
                        'studentName',
                        'studentEmail',
                        'quotaExpiresAt',
                        'isActive',
                        'quotaStatus',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_list_all_students_never_returns_students_from_other_trainers(): void
    {
        // Create gym for other trainer
        $otherGymId = Str::uuid()->toString();
        GymEloquentModel::create([
            'id' => $otherGymId,
            'trainer_id' => $this->otherTrainerId,
            'name' => 'Other Trainer Gym',
            'address' => 'Other Address',
            'locality' => 'Sevilla',
            'province' => 'Andalucía',
            'country' => 'España',
            'is_active' => true,
        ]);

        // Enroll student in current trainer's gym
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
            'is_active' => true,
        ]);

        // Enroll student in other trainer's gym
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $otherGymId,
            'student_id' => $this->anotherStudentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/students');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'gymId' => $this->gymId,
                        'studentId' => $this->studentId,
                    ],
                ],
            ]);
    }

    public function test_quota_status_is_expiring_soon_for_date_within_7_days(): void
    {
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+5 days')),
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/gyms/{$this->gymId}/students");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'quotaStatus' => 'expiring_soon',
                    ],
                ],
            ]);
    }

    public function test_quota_status_is_expired_for_past_date(): void
    {
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('-5 days')),
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/gyms/{$this->gymId}/students");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'quotaStatus' => 'expired',
                    ],
                ],
            ]);
    }

    public function test_validation_requires_email(): void
    {
        $enrollData = [
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_validation_requires_quota_expires_at(): void
    {
        $enrollData = [
            'email' => 'student@test.com',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students", $enrollData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quota_expires_at']);
    }
}
