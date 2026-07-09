<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineAssignmentEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineEloquentModel;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoutineAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private string $trainerId;
    private string $otherTrainerId;
    private string $studentId;
    private string $inactiveStudentId;
    private string $token;
    private string $gymId;
    private string $routineId;
    private string $otherRoutineId;
    private string $anotherTrainerRoutineId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create trainer
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

        // Create students
        $this->studentId = Str::uuid()->toString();
        UserEloquentModel::create([
            'id' => $this->studentId,
            'email' => 'student@test.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Active',
            'last_name' => 'Student',
            'user_type' => 'student',
            'birth_date' => '2000-01-01',
            'gender' => 'male',
            'gym_goals' => 'Build muscle',
            'email_verified_at' => now(),
        ]);

        $this->inactiveStudentId = Str::uuid()->toString();
        UserEloquentModel::create([
            'id' => $this->inactiveStudentId,
            'email' => 'inactive@test.com',
            'password' => bcrypt('Password123!'),
            'name' => 'Inactive',
            'last_name' => 'Student',
            'user_type' => 'student',
            'birth_date' => '2000-01-01',
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

        // Enroll active student
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->studentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
            'is_active' => true,
        ]);

        // Enroll inactive student
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gymId,
            'student_id' => $this->inactiveStudentId,
            'quota_expires_at' => date('Y-m-d', strtotime('+30 days')),
            'is_active' => false,
        ]);

        // Create routines
        $this->routineId = Str::uuid()->toString();
        RoutineEloquentModel::create([
            'id' => $this->routineId,
            'trainer_id' => $this->trainerId,
            'name' => 'Test Routine',
            'description' => 'A test routine',
            'difficulty' => 'intermediate',
        ]);

        $this->otherRoutineId = Str::uuid()->toString();
        RoutineEloquentModel::create([
            'id' => $this->otherRoutineId,
            'trainer_id' => $this->trainerId,
            'name' => 'Another Routine',
            'description' => 'Another test routine',
            'difficulty' => 'advanced',
        ]);

        $this->anotherTrainerRoutineId = Str::uuid()->toString();
        RoutineEloquentModel::create([
            'id' => $this->anotherTrainerRoutineId,
            'trainer_id' => $this->otherTrainerId,
            'name' => 'Other Trainer Routine',
            'description' => 'Routine from another trainer',
            'difficulty' => 'beginner',
        ]);
    }

    public function test_trainer_can_assign_routine_to_active_student(): void
    {
        $assignmentData = [
            'routine_id' => $this->routineId,
            'is_current' => true,
            'starts_at' => date('Y-m-d'),
            'notes' => 'Rutina de hipertrofia para 8 semanas',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines", $assignmentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'routineId',
                    'studentId',
                    'gymId',
                    'isCurrent',
                    'assignedAt',
                    'startsAt',
                    'notes',
                ],
            ])
            ->assertJson([
                'data' => [
                    'routineId' => $this->routineId,
                    'studentId' => $this->studentId,
                    'gymId' => $this->gymId,
                    'isCurrent' => true,
                    'notes' => 'Rutina de hipertrofia para 8 semanas',
                ],
            ]);

        $this->assertDatabaseHas('routine_assignments', [
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => true,
        ]);
    }

    public function test_cannot_assign_routine_to_inactive_student(): void
    {
        $assignmentData = [
            'routine_id' => $this->routineId,
            'is_current' => true,
            'starts_at' => date('Y-m-d'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students/{$this->inactiveStudentId}/routines", $assignmentData);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('routine_assignments', [
            'routine_id' => $this->routineId,
            'student_id' => $this->inactiveStudentId,
        ]);
    }

    public function test_cannot_assign_routine_from_another_trainer(): void
    {
        $assignmentData = [
            'routine_id' => $this->anotherTrainerRoutineId,
            'is_current' => true,
            'starts_at' => date('Y-m-d'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines", $assignmentData);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('routine_assignments', [
            'routine_id' => $this->anotherTrainerRoutineId,
            'student_id' => $this->studentId,
        ]);
    }

    public function test_cannot_assign_already_assigned_routine(): void
    {
        // Create first assignment
        RoutineAssignmentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => true,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d'),
        ]);

        // Try to assign same routine again
        $assignmentData = [
            'routine_id' => $this->routineId,
            'is_current' => true,
            'starts_at' => date('Y-m-d'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines", $assignmentData);

        $response->assertStatus(422);
    }

    public function test_trainer_can_list_student_routines_ordered_current_first(): void
    {
        // Create assignments
        $assignment1Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment1Id,
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => false,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d', strtotime('-7 days')),
        ]);

        $assignment2Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment2Id,
            'routine_id' => $this->otherRoutineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => true,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d'),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'routineId',
                        'studentId',
                        'gymId',
                        'isCurrent',
                        'assignedAt',
                        'startsAt',
                        'notes',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertTrue($data[0]['isCurrent']); // Current routine should be first
        $this->assertFalse($data[1]['isCurrent']);
    }

    public function test_trainer_can_update_assignment_notes_and_starts_at(): void
    {
        $assignmentId = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignmentId,
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => true,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d'),
            'notes' => 'Old notes',
        ]);

        $updateData = [
            'starts_at' => date('Y-m-d', strtotime('+7 days')),
            'notes' => 'Updated notes',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines/{$assignmentId}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'notes' => 'Updated notes',
                    'startsAt' => date('Y-m-d', strtotime('+7 days')),
                ],
            ]);

        $this->assertDatabaseHas('routine_assignments', [
            'id' => $assignmentId,
            'notes' => 'Updated notes',
            'starts_at' => date('Y-m-d', strtotime('+7 days')),
        ]);
    }

    public function test_setting_is_current_true_unsets_previous_current(): void
    {
        // Create two assignments
        $assignment1Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment1Id,
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => true,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d', strtotime('-7 days')),
        ]);

        $assignment2Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment2Id,
            'routine_id' => $this->otherRoutineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => false,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d'),
        ]);

        // Update assignment2 to be current
        $updateData = [
            'is_current' => true,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines/{$assignment2Id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('routine_assignments', [
            'id' => $assignment1Id,
            'is_current' => false,
        ]);

        $this->assertDatabaseHas('routine_assignments', [
            'id' => $assignment2Id,
            'is_current' => true,
        ]);
    }

    public function test_trainer_can_delete_assignment(): void
    {
        $assignmentId = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignmentId,
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => false,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d'),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines/{$assignmentId}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('routine_assignments', [
            'id' => $assignmentId,
        ]);
    }

    public function test_deleting_current_assignment_sets_most_recent_as_current(): void
    {
        // Create assignments
        $assignment1Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment1Id,
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => false,
            'assigned_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'starts_at' => date('Y-m-d', strtotime('-10 days')),
        ]);

        $assignment2Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment2Id,
            'routine_id' => $this->otherRoutineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => true,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d'),
        ]);

        // Delete current assignment
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines/{$assignment2Id}");

        $response->assertStatus(204);

        // The most recent remaining assignment should become current
        $this->assertDatabaseHas('routine_assignments', [
            'id' => $assignment1Id,
            'is_current' => true,
        ]);
    }

    public function test_trainer_can_set_routine_as_current_manually(): void
    {
        // Create two assignments
        $assignment1Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment1Id,
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => true,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d', strtotime('-7 days')),
        ]);

        $assignment2Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment2Id,
            'routine_id' => $this->otherRoutineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => false,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d'),
        ]);

        // Manually set assignment2 as current
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/gyms/{$this->gymId}/students/{$this->studentId}/routines/{$assignment2Id}/set-current");

        $response->assertStatus(200);

        $this->assertDatabaseHas('routine_assignments', [
            'id' => $assignment1Id,
            'is_current' => false,
        ]);

        $this->assertDatabaseHas('routine_assignments', [
            'id' => $assignment2Id,
            'is_current' => true,
        ]);
    }

    public function test_scheduler_updates_current_routines_based_on_starts_at(): void
    {
        // Create assignments with future starts_at dates
        $assignment1Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment1Id,
            'routine_id' => $this->routineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => true,
            'assigned_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'starts_at' => date('Y-m-d', strtotime('-10 days')),
        ]);

        $assignment2Id = Str::uuid()->toString();
        RoutineAssignmentEloquentModel::create([
            'id' => $assignment2Id,
            'routine_id' => $this->otherRoutineId,
            'student_id' => $this->studentId,
            'gym_id' => $this->gymId,
            'is_current' => false,
            'assigned_at' => now(),
            'starts_at' => date('Y-m-d', strtotime('-1 day')), // Started yesterday
        ]);

        // Run the scheduler command
        $this->artisan('routines:update-current')
            ->assertExitCode(0);

        // assignment2 should now be current
        $this->assertDatabaseHas('routine_assignments', [
            'id' => $assignment1Id,
            'is_current' => false,
        ]);

        $this->assertDatabaseHas('routine_assignments', [
            'id' => $assignment2Id,
            'is_current' => true,
        ]);
    }
}
