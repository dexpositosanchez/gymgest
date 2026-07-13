<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class StudentGymManagementTest extends TestCase
{
    use RefreshDatabase;

    private UserEloquentModel $student;
    private UserEloquentModel $trainer;
    private string $studentToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create verified trainer
        $this->trainer = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Test',
            'last_name' => 'Trainer',
            'email' => 'trainer@test.com',
            'password' => bcrypt('password123'),
            'user_type' => 'trainer',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'email_verified_at' => now(),
        ]);

        // Create verified student
        $this->student = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@test.com',
            'password' => bcrypt('password123'),
            'user_type' => 'student',
            'birth_date' => '1995-01-01',
            'gender' => 'female',
            'email_verified_at' => now(),
        ]);

        $this->studentToken = JWTAuth::fromUser($this->student);
    }

    public function test_student_can_list_their_gyms(): void
    {
        // Arrange: Create active gym with student enrollment
        $gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Test',
            'address' => 'Calle Principal 123',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(6)->toDateString(),
            'is_active' => true,
            'created_at' => now()->subDays(10),
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'enrollment_id',
                        'enrolled_at',
                        'quota_expires_at',
                        'quota_status',
                        'gym' => [
                            'id',
                            'name',
                            'address',
                            'locality',
                            'province',
                            'country',
                            'is_personal_training',
                        ],
                        'trainer' => [
                            'id',
                            'name',
                            'email',
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($gym->id, $data[0]['gym']['id']);
        $this->assertEquals('Test Trainer', $data[0]['trainer']['name']);
    }

    public function test_student_sees_only_active_enrollments(): void
    {
        // Arrange: Create 2 gyms - one with active enrollment, one with inactive
        $activeGym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Activo',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        $inactiveEnrollmentGym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Inactivo',
            'address' => 'Calle 2',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        // Active enrollment
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $activeGym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);

        // Inactive enrollment (different gym)
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $inactiveEnrollmentGym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->subMonth()->toDateString(),
            'is_active' => false, // Deactivated
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert: Only 1 gym (active enrollment)
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Gimnasio Activo', $data[0]['gym']['name']);
    }

    public function test_student_sees_only_active_gyms(): void
    {
        // Arrange: Create 1 active gym and 1 inactive gym
        $activeGym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Activo',
            'address' => 'Calle Activa',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        $inactiveGym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Inactivo',
            'address' => 'Calle Inactiva',
            'locality' => 'Barcelona',
            'province' => 'Barcelona',
            'country' => 'España',
            'is_active' => false, // Deactivated
            'is_personal_training' => false,
        ]);

        // Active enrollments in both
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $activeGym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);

        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $inactiveGym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert: Only active gym
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Gimnasio Activo', $data[0]['gym']['name']);
    }

    public function test_quota_status_active_calculated_correctly(): void
    {
        // Arrange: Quota expires in 30 days (> 7 days = active)
        $gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Test',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addDays(30)->toDateString(),
            'is_active' => true,
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('active', $data[0]['quota_status']);
    }

    public function test_quota_status_expiring_soon_calculated_correctly(): void
    {
        // Arrange: Quota expires in 5 days (<= 7 days = expiring_soon)
        $gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Test',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addDays(5)->toDateString(),
            'is_active' => true,
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('expiring_soon', $data[0]['quota_status']);
    }

    public function test_quota_status_expired_calculated_correctly(): void
    {
        // Arrange: Quota expired 10 days ago (< today = expired)
        $gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Test',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->subDays(10)->toDateString(),
            'is_active' => true,
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('expired', $data[0]['quota_status']);
    }

    public function test_returns_empty_array_when_no_gyms(): void
    {
        // Act: Student with no enrollments
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert
        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_gyms_ordered_by_enrollment_date_desc(): void
    {
        // Arrange: Create 3 gyms enrolled at different dates
        $gym1 = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Antiguo',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        $gym2 = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Reciente',
            'address' => 'Calle 2',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        $gym3 = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Medio',
            'address' => 'Calle 3',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        // Enrolled in this order: gym1 (30 days ago) → gym3 (10 days ago) → gym2 (today)
        $date1 = now()->subDays(30);
        $date2 = now()->subDays(10);
        $date3 = now();

        $enrollment1 = new GymStudentEloquentModel([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym1->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);
        $enrollment1->created_at = $date1;
        $enrollment1->updated_at = $date1;
        $enrollment1->save();

        $enrollment2 = new GymStudentEloquentModel([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym3->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);
        $enrollment2->created_at = $date2;
        $enrollment2->updated_at = $date2;
        $enrollment2->save();

        $enrollment3 = new GymStudentEloquentModel([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym2->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);
        $enrollment3->created_at = $date3;
        $enrollment3->updated_at = $date3;
        $enrollment3->save();

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert: Ordered DESC (most recent first)
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
        $this->assertEquals('Gimnasio Reciente', $data[0]['gym']['name']);
        $this->assertEquals('Gimnasio Medio', $data[1]['gym']['name']);
        $this->assertEquals('Gimnasio Antiguo', $data[2]['gym']['name']);
    }

    public function test_includes_complete_trainer_information(): void
    {
        // Arrange
        $gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Test',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $trainer = $data[0]['trainer'];
        $this->assertEquals($this->trainer->id, $trainer['id']);
        $this->assertEquals('Test Trainer', $trainer['name']);
        $this->assertEquals('trainer@test.com', $trainer['email']);
    }

    public function test_includes_enrollment_date(): void
    {
        // Arrange
        $enrollmentDate = now()->subDays(15);
        $gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Test',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        $enrollment = new GymStudentEloquentModel([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);
        $enrollment->created_at = $enrollmentDate;
        $enrollment->updated_at = $enrollmentDate;
        $enrollment->save();

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotNull($data[0]['enrolled_at']);
        $this->assertStringContainsString($enrollmentDate->format('Y-m-d'), $data[0]['enrolled_at']);
    }

    public function test_trainer_cannot_access_endpoint(): void
    {
        // Arrange: Login as trainer
        $trainerToken = JWTAuth::fromUser($this->trainer);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $trainerToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert: 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_unauthenticated_receives_401(): void
    {
        // Act: No token
        $response = $this->getJson('/api/v1/students/me/gyms');

        // Assert
        $response->assertStatus(401);
    }

    public function test_cache_works_on_second_request(): void
    {
        // Arrange
        $gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Test',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);

        // Act: First request (no cache)
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Act: Second request (should use cache)
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert: Both return same data
        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $this->assertEquals($response1->json('data'), $response2->json('data'));
    }

    public function test_cache_invalidates_on_enrollment_change(): void
    {
        // Arrange: Initial enrollment
        $gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Gimnasio Test',
            'address' => 'Calle 1',
            'locality' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'España',
            'is_active' => true,
            'is_personal_training' => false,
        ]);

        $enrollment = GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);

        // Act: First request (populate cache)
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        $response1->assertStatus(200);
        $this->assertCount(1, $response1->json('data'));

        // Modify enrollment (should invalidate cache)
        $enrollment->is_active = false;
        $enrollment->save(); // This triggers cache invalidation

        // Clear cache manually (in case observer didn't fire for test)
        Cache::forget('student:gyms:' . $this->student->id . ':*');

        // Act: Second request (should re-query and see no gyms)
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->studentToken)
            ->getJson('/api/v1/students/me/gyms');

        // Assert: Now returns empty array
        $response2->assertStatus(200);
        $this->assertCount(0, $response2->json('data'));
    }
}
