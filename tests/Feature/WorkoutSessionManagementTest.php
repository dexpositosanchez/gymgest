<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use App\Infrastructure\Persistence\Eloquent\ExerciseEloquentModel;
use App\Infrastructure\Persistence\Eloquent\MuscleGroupEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineDayEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineDayExerciseEloquentModel;
use App\Infrastructure\Persistence\Eloquent\ExerciseSetEloquentModel;
use App\Infrastructure\Persistence\Eloquent\RoutineAssignmentEloquentModel;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class WorkoutSessionManagementTest extends TestCase
{
    use RefreshDatabase;

    private UserEloquentModel $student;
    private UserEloquentModel $trainer;
    private GymEloquentModel $gym;
    private RoutineEloquentModel $routine;
    private RoutineAssignmentEloquentModel $assignment;
    private string $studentToken;
    private string $trainerToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create trainer
        $this->trainer = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'email' => 'trainer@example.com',
            'password' => bcrypt('password'),
            'name' => 'Trainer',
            'last_name' => 'Test',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'user_type' => 'trainer',
            'email_verified_at' => now(),
        ]);

        // Create student
        $this->student = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'name' => 'Student',
            'last_name' => 'Test',
            'birth_date' => '2000-01-01',
            'gender' => 'male',
            'user_type' => 'student',
            'gym_goals' => 'Get fit',
            'email_verified_at' => now(),
        ]);

        // Create gym
        $this->gym = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Test Gym',
            'address' => 'Test Address',
            'locality' => 'Test City',
            'province' => 'Test Province',
            'country' => 'Spain',
            'is_personal_training' => false,
            'is_active' => true,
        ]);

        // Enroll student in gym
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gym->id,
            'student_id' => $this->student->id,
            'quota_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Create muscle group and exercise
        $muscleGroup = MuscleGroupEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Chest',
            'description' => 'Chest muscles',
        ]);

        $exercise = ExerciseEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'muscle_group_id' => $muscleGroup->id,
            'name' => 'Bench Press',
            'description' => 'Bench press exercise',
            'is_default' => true,
        ]);

        // Create routine with 1 day and 1 exercise with 3 sets
        $this->routine = RoutineEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer->id,
            'name' => 'Test Routine',
            'description' => 'Test routine description',
            'difficulty' => 'beginner',
        ]);

        $routineDay = RoutineDayEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_id' => $this->routine->id,
            'day_number' => 1,
            'name' => 'Day 1',
        ]);

        $routineDayExercise = RoutineDayExerciseEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_day_id' => $routineDay->id,
            'exercise_id' => $exercise->id,
            'order_index' => 1,
            'notes' => null,
        ]);

        // Create 3 sets for the exercise
        for ($i = 1; $i <= 3; $i++) {
            ExerciseSetEloquentModel::create([
                'id' => Str::uuid()->toString(),
                'routine_day_exercise_id' => $routineDayExercise->id,
                'set_number' => $i,
                'reps' => 10,
            ]);
        }

        // Assign routine to student
        $this->assignment = RoutineAssignmentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_id' => $this->routine->id,
            'student_id' => $this->student->id,
            'gym_id' => $this->gym->id,
            'assigned_at' => now(),
            'starts_at' => now()->subDay(),
            'is_current' => true,
            'notes' => null,
        ]);

        // Generate tokens
        $this->studentToken = JWTAuth::fromUser($this->student);
        $this->trainerToken = JWTAuth::fromUser($this->trainer);
    }

    public function test_student_can_start_workout_session(): void
    {
        $response = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
            'notes' => 'First workout!',
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'routine_assignment_id',
                'day_number',
                'started_at',
                'is_active',
            ]);
    }

    public function test_student_cannot_start_session_with_invalid_day_number(): void
    {
        $response = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 99, // Invalid day
            'notes' => null,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(422);
    }

    public function test_student_cannot_start_session_when_already_has_active_session(): void
    {
        // Start first session
        $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        // Try to start second session
        $response = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(409)
            ->assertJson(['error' => 'Ya tienes una sesión activa']);
    }

    public function test_student_can_view_active_session_with_exercises(): void
    {
        // Start session
        $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        // Get active session
        $response = $this->getJson('/api/v1/students/me/workout-sessions/active', [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'day_number',
                'exercises' => [
                    '*' => [
                        'exercise_id',
                        'name',
                        'total_sets',
                        'completed_sets',
                        'is_completed',
                    ]
                ]
            ]);
    }

    public function test_student_without_active_session_receives_404(): void
    {
        $response = $this->getJson('/api/v1/students/me/workout-sessions/active', [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(404);
    }

    public function test_student_can_execute_set_without_weight(): void
    {
        // Start session
        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');
        $exerciseId = $this->routine->days[0]->exercises[0]->exercise_id;

        $response = $this->postJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => null,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(201);
    }

    public function test_student_can_execute_set_with_weight(): void
    {
        // Start session
        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');
        $exerciseId = $this->routine->days[0]->exercises[0]->exercise_id;

        $response = $this->postJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => 50.0,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(201);

        // Verify weight history was created
        $this->assertDatabaseHas('exercise_weight_history', [
            'student_id' => $this->student->id,
            'exercise_id' => $exerciseId,
            'reps' => 10,
            'weight' => 50.0,
        ]);
    }

    public function test_weight_history_updates_when_weight_changes(): void
    {
        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');
        $exerciseId = $this->routine->days[0]->exercises[0]->exercise_id;

        // Execute first set with 50kg
        $this->postJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => 50.0,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        // Execute second set with 55kg (different weight)
        $this->postJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'set_number' => 2,
            'reps_completed' => 10,
            'weight_used' => 55.0,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        // Weight history should be updated to 55kg
        $this->assertDatabaseHas('exercise_weight_history', [
            'student_id' => $this->student->id,
            'exercise_id' => $exerciseId,
            'reps' => 10,
            'weight' => 55.0,
        ]);
    }

    public function test_weight_history_does_not_update_when_weight_is_same(): void
    {
        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');
        $exerciseId = $this->routine->days[0]->exercises[0]->exercise_id;

        // Execute first set
        $this->postJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => 50.0,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $firstTimestamp = \DB::table('exercise_weight_history')
            ->where('student_id', $this->student->id)
            ->value('last_used_at');

        sleep(1);

        // Execute second set with same weight
        $this->postJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'set_number' => 2,
            'reps_completed' => 10,
            'weight_used' => 50.0,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $secondTimestamp = \DB::table('exercise_weight_history')
            ->where('student_id', $this->student->id)
            ->value('last_used_at');

        // Timestamp should NOT have changed
        $this->assertEquals($firstTimestamp, $secondTimestamp);
    }

    public function test_suggested_weight_is_null_when_no_history_exists(): void
    {
        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');
        $exerciseId = $this->routine->days[0]->exercises[0]->exercise_id;

        $response = $this->getJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(200);
        $sets = $response->json('sets');

        foreach ($sets as $set) {
            $this->assertNull($set['suggested_weight']);
        }
    }

    public function test_suggested_weight_is_last_used_weight(): void
    {
        // Create history entry
        \DB::table('exercise_weight_history')->insert([
            'id' => Str::uuid()->toString(),
            'student_id' => $this->student->id,
            'exercise_id' => $this->routine->days[0]->exercises[0]->exercise_id,
            'reps' => 10,
            'weight' => 75.0,
            'last_used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');
        $exerciseId = $this->routine->days[0]->exercises[0]->exercise_id;

        $response = $this->getJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(200);
        $sets = $response->json('sets');

        foreach ($sets as $set) {
            $this->assertEquals(75.0, $set['suggested_weight']);
        }
    }

    public function test_student_can_mark_exercise_as_complete_manually(): void
    {
        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');
        $exerciseId = $this->routine->days[0]->exercises[0]->exercise_id;

        $response = $this->putJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/mark-complete", [], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(204);
    }

    public function test_student_can_finish_session_anytime(): void
    {
        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');

        $response = $this->putJson("/api/v1/students/me/workout-sessions/{$sessionId}/finish", [
            'notes' => 'Good workout!',
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('workout_sessions', [
            'id' => $sessionId,
            'is_active' => false,
        ]);
    }

    public function test_cannot_add_sets_to_finished_session(): void
    {
        $sessionResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $sessionResponse->json('id');
        $exerciseId = $this->routine->days[0]->exercises[0]->exercise_id;

        // Finish session
        $this->putJson("/api/v1/students/me/workout-sessions/{$sessionId}/finish", [], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        // Try to add set
        $response = $this->postJson("/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets", [
            'set_number' => 1,
            'reps_completed' => 10,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(422);
    }

    public function test_student_can_view_workout_history(): void
    {
        // Create finished session
        $session = \DB::table('workout_sessions')->insertGetId([
            'id' => Str::uuid()->toString(),
            'routine_assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'day_number' => 1,
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'is_active' => false,
            'notes' => 'Test session',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/students/me/workout-sessions', [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'day_number',
                        'started_at',
                        'finished_at',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ]
            ]);
    }

    public function test_trainer_cannot_access_student_endpoints(): void
    {
        $response = $this->getJson('/api/v1/students/me/workout-sessions/active', [
            'Authorization' => 'Bearer ' . $this->trainerToken,
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'This endpoint is only for students']);
    }

    public function test_cannot_execute_same_set_twice(): void
    {
        // Start workout session
        $startResponse = $this->postJson('/api/v1/students/me/workout-sessions', [
            'routine_assignment_id' => $this->assignment->id,
            'day_number' => 1,
        ], [
            'Authorization' => 'Bearer ' . $this->studentToken,
        ]);

        $sessionId = $startResponse->json('id');
        $exerciseId = $this->routine->days->first()->exercises->first()->exercise_id;

        // Execute set number 1
        $firstExecution = $this->postJson(
            "/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets",
            [
                'set_number' => 1,
                'reps_completed' => 10,
                'weight_used' => 50.0,
            ],
            [
                'Authorization' => 'Bearer ' . $this->studentToken,
            ]
        );

        $firstExecution->assertStatus(201);

        // Try to execute the same set again
        $secondExecution = $this->postJson(
            "/api/v1/students/me/workout-sessions/{$sessionId}/exercises/{$exerciseId}/sets",
            [
                'set_number' => 1,
                'reps_completed' => 10,
                'weight_used' => 50.0,
            ],
            [
                'Authorization' => 'Bearer ' . $this->studentToken,
            ]
        );

        $secondExecution->assertStatus(422)
            ->assertJson(['error' => 'Esta serie ya ha sido ejecutada']);
    }
}
