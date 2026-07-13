<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineAssignmentEloquentModel;
use App\Application\RoutineAssignment\Services\RoutineAssignmentCacheService;

class StudentRoutineManagementTest extends TestCase
{
    use RefreshDatabase;

    private UserEloquentModel $trainer;
    private UserEloquentModel $student;
    private GymEloquentModel $gym;
    private RoutineEloquentModel $routine;
    private RoutineEloquentModel $routine2;
    private string $studentToken;
    private string $trainerToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create trainer
        $this->trainer = UserEloquentModel::create([
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

        // Create student
        $this->student = UserEloquentModel::create([
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

        // Generate JWT tokens
        $this->trainerToken = auth()->login($this->trainer);
        $this->studentToken = auth()->login($this->student);

        // Create gym
        $this->gym = GymEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Test Gym',
            'address' => '123 Test St',
            'locality' => 'Test City',
            'province' => 'Test Province',
            'country' => 'Test Country',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        // Create routines
        $this->routine = RoutineEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Test Routine',
            'description' => 'Test Description',
            'difficulty' => 'intermediate',
        ]);

        $this->routine2 = RoutineEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Test Routine 2',
            'description' => 'Test Description 2',
            'difficulty' => 'beginner',
        ]);
    }

    public function test_student_can_list_all_assigned_routines(): void
    {
        // Arrange: Enroll student and assign routine
        GymStudentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'gym_id' => $this->gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        RoutineAssignmentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'routine_id' => $this->routine->id,
            'student_id' => $this->student->id,
            'gym_id' => $this->gym->id,
            'starts_at' => now()->toDateString(),
            'is_current' => true,
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/routines');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'starts_at',
                        'is_current',
                        'assigned_at',
                        'routine' => ['id', 'name', 'difficulty'],
                        'gym' => ['id', 'name', 'is_personal_training'],
                        'trainer' => ['id', 'name', 'email'],
                    ],
                ],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonCount(1, 'data');
    }

    public function test_student_can_list_only_current_routines(): void
    {
        // Arrange: Enroll student and assign 2 routines (1 current, 1 not current)
        GymStudentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'gym_id' => $this->gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        RoutineAssignmentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'routine_id' => $this->routine->id,
            'student_id' => $this->student->id,
            'gym_id' => $this->gym->id,
            'starts_at' => now()->toDateString(),
            'is_current' => true,
        ]);

        RoutineAssignmentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'routine_id' => $this->routine2->id,
            'student_id' => $this->student->id,
            'gym_id' => $this->gym->id,
            'starts_at' => now()->subMonth()->toDateString(),
            'is_current' => false,
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/routines/current');

        // Assert: Should only return 1 (current)
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_current', true);
    }

    public function test_student_only_sees_routines_from_active_gyms(): void
    {
        // Arrange: Enroll student (active) and assign routine
        GymStudentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'gym_id' => $this->gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        RoutineAssignmentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'routine_id' => $this->routine->id,
            'student_id' => $this->student->id,
            'gym_id' => $this->gym->id,
            'starts_at' => now()->toDateString(),
            'is_current' => true,
        ]);

        // Act 1: Student is active, should see routine
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/routines');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Arrange 2: Deactivate student
        GymStudentEloquentModel::where('student_id', $this->student->id)
            ->update(['is_active' => false]);

        // Manually invalidate cache since bulk updates don't trigger model events
        $cacheService = app(RoutineAssignmentCacheService::class);
        $cacheService->invalidate($this->student->id);

        // Verify deactivation worked
        $gymStudent = GymStudentEloquentModel::where('student_id', $this->student->id)->first();
        $this->assertFalse($gymStudent->is_active, 'Gym student should be deactivated');

        // Act 2: Student is inactive, should NOT see routine
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/routines');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_user_receives_401(): void
    {
        $response = $this->getJson('/api/v1/students/me/routines');

        $response->assertStatus(401);
    }

    public function test_trainer_receives_403_forbidden(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->trainerToken)
            ->getJson('/api/v1/students/me/routines');

        $response->assertStatus(403)
            ->assertJson(['error' => 'This endpoint is only for students']);
    }

    public function test_student_can_filter_by_gym_id(): void
    {
        // Arrange: Create 2 gyms, enroll student in both, assign routines to both
        $gym2 = GymEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Test Gym 2',
            'address' => '456 Test St',
            'locality' => 'Test City',
            'province' => 'Test Province',
            'country' => 'Test Country',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        GymStudentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'gym_id' => $this->gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        GymStudentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'gym_id' => $gym2->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        RoutineAssignmentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'routine_id' => $this->routine->id,
            'student_id' => $this->student->id,
            'gym_id' => $this->gym->id,
            'starts_at' => now()->toDateString(),
            'is_current' => true,
        ]);

        RoutineAssignmentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'routine_id' => $this->routine->id,
            'student_id' => $this->student->id,
            'gym_id' => $gym2->id,
            'starts_at' => now()->toDateString(),
            'is_current' => true,
        ]);

        // Act: Filter by gym1
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson("/api/v1/students/me/routines?gym_id={$this->gym->id}");

        // Assert: Should only return 1 (from gym1)
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.gym.id', $this->gym->id);
    }

    public function test_returns_empty_array_when_no_routines(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/routines');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_pagination_works_correctly(): void
    {
        // Arrange: Enroll student and assign 15 routines
        GymStudentEloquentModel::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'gym_id' => $this->gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        for ($i = 0; $i < 15; $i++) {
            // Create unique routine for each assignment to avoid unique constraint violation
            $routine = RoutineEloquentModel::create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'trainer_id' => $this->trainer->id,
                'name' => 'Routine ' . ($i + 1),
                'description' => 'Description ' . ($i + 1),
                'difficulty' => 'intermediate',
            ]);

            RoutineAssignmentEloquentModel::create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'routine_id' => $routine->id,
                'student_id' => $this->student->id,
                'gym_id' => $this->gym->id,
                'starts_at' => now()->subDays($i)->toDateString(),
                'is_current' => $i === 0,
            ]);
        }

        // Act: Request page 1 with 10 per page
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/routines?page=1&per_page=10');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.last_page', 2);
    }
}
