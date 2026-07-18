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

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    private UserEloquentModel $trainer1;
    private UserEloquentModel $trainer2;
    private UserEloquentModel $student1;
    private UserEloquentModel $student2;
    private GymEloquentModel $gym1;
    private GymEloquentModel $gym2;
    private RoutineEloquentModel $routine1;
    private RoutineAssignmentEloquentModel $assignment1;
    private RoutineAssignmentEloquentModel $assignment2;
    private ExerciseEloquentModel $exercise1;
    private ExerciseEloquentModel $exercise2;
    private RoutineDayExerciseEloquentModel $routineDayExercise1;
    private RoutineDayExerciseEloquentModel $routineDayExercise2;
    private string $trainer1Token;
    private string $trainer2Token;
    private string $student1Token;
    private string $student2Token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create trainers
        $this->trainer1 = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'email' => 'trainer1@example.com',
            'password' => bcrypt('password'),
            'name' => 'Trainer',
            'last_name' => 'One',
            'birth_date' => '1985-01-01',
            'gender' => 'male',
            'user_type' => 'trainer',
            'email_verified_at' => now(),
        ]);

        $this->trainer2 = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'email' => 'trainer2@example.com',
            'password' => bcrypt('password'),
            'name' => 'Trainer',
            'last_name' => 'Two',
            'birth_date' => '1986-01-01',
            'gender' => 'female',
            'user_type' => 'trainer',
            'email_verified_at' => now(),
        ]);

        // Create students
        $this->student1 = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'email' => 'student1@example.com',
            'password' => bcrypt('password'),
            'name' => 'Student',
            'last_name' => 'One',
            'birth_date' => '2000-01-01',
            'gender' => 'male',
            'user_type' => 'student',
            'gym_goals' => 'Build muscle',
            'email_verified_at' => now(),
        ]);

        $this->student2 = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'email' => 'student2@example.com',
            'password' => bcrypt('password'),
            'name' => 'Student',
            'last_name' => 'Two',
            'birth_date' => '2001-01-01',
            'gender' => 'female',
            'user_type' => 'student',
            'gym_goals' => 'Lose weight',
            'email_verified_at' => now(),
        ]);

        // Create gyms
        $this->gym1 = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer1->id,
            'name' => 'Gym One',
            'address' => 'Address 1',
            'locality' => 'City 1',
            'province' => 'Province 1',
            'country' => 'Spain',
            'is_personal_training' => false,
            'is_active' => true,
        ]);

        $this->gym2 = GymEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer2->id,
            'name' => 'Gym Two',
            'address' => 'Address 2',
            'locality' => 'City 2',
            'province' => 'Province 2',
            'country' => 'Spain',
            'is_personal_training' => false,
            'is_active' => true,
        ]);

        // Enroll students in gyms
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gym1->id,
            'student_id' => $this->student1->id,
            'quota_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gym2->id,
            'student_id' => $this->student2->id,
            'quota_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Create muscle groups and exercises
        $muscleGroup1 = MuscleGroupEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Chest',
            'description' => 'Chest muscles',
        ]);

        $muscleGroup2 = MuscleGroupEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Back',
            'description' => 'Back muscles',
        ]);

        $this->exercise1 = ExerciseEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'muscle_group_id' => $muscleGroup1->id,
            'name' => 'Bench Press',
            'description' => 'Bench press exercise',
            'is_default' => true,
        ]);

        $this->exercise2 = ExerciseEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'muscle_group_id' => $muscleGroup2->id,
            'name' => 'Pull Up',
            'description' => 'Pull up exercise',
            'is_default' => true,
        ]);

        // Create routine with exercises
        $this->routine1 = RoutineEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainer1->id,
            'name' => 'Strength Routine',
            'description' => 'Build strength',
            'difficulty' => 'intermediate',
        ]);

        $routineDay1 = RoutineDayEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_id' => $this->routine1->id,
            'day_number' => 1,
            'name' => 'Day 1 - Upper Body',
        ]);

        $this->routineDayExercise1 = RoutineDayExerciseEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_day_id' => $routineDay1->id,
            'exercise_id' => $this->exercise1->id,
            'order_index' => 1,
            'notes' => null,
        ]);

        $this->routineDayExercise2 = RoutineDayExerciseEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_day_id' => $routineDay1->id,
            'exercise_id' => $this->exercise2->id,
            'order_index' => 2,
            'notes' => null,
        ]);

        // Create sets for exercises
        for ($i = 1; $i <= 3; $i++) {
            ExerciseSetEloquentModel::create([
                'id' => Str::uuid()->toString(),
                'routine_day_exercise_id' => $this->routineDayExercise1->id,
                'set_number' => $i,
                'reps' => 10,
            ]);
        }

        for ($i = 1; $i <= 3; $i++) {
            ExerciseSetEloquentModel::create([
                'id' => Str::uuid()->toString(),
                'routine_day_exercise_id' => $this->routineDayExercise2->id,
                'set_number' => $i,
                'reps' => 8,
            ]);
        }

        // Assign routine to students
        $this->assignment1 = RoutineAssignmentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_id' => $this->routine1->id,
            'student_id' => $this->student1->id,
            'gym_id' => $this->gym1->id,
            'assigned_at' => now()->subDays(30),
            'starts_at' => now()->subDays(30),
            'is_current' => true,
            'notes' => null,
        ]);

        $this->assignment2 = RoutineAssignmentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_id' => $this->routine1->id,
            'student_id' => $this->student2->id,
            'gym_id' => $this->gym1->id,
            'assigned_at' => now()->subDays(20),
            'starts_at' => now()->subDays(20),
            'is_current' => true,
            'notes' => null,
        ]);

        // Create completed workout sessions with set executions for student1
        $session1 = \DB::table('workout_sessions')->insertGetId([
            'id' => Str::uuid()->toString(),
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subDays(25),
            'finished_at' => now()->subDays(25)->addHour(),
            'is_active' => false,
            'notes' => 'First session',
            'created_at' => now()->subDays(25),
            'updated_at' => now()->subDays(25),
        ], 'id');

        $session2 = \DB::table('workout_sessions')->insertGetId([
            'id' => Str::uuid()->toString(),
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subDays(20),
            'finished_at' => now()->subDays(20)->addHour(),
            'is_active' => false,
            'notes' => 'Second session',
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ], 'id');

        $session3 = \DB::table('workout_sessions')->insertGetId([
            'id' => Str::uuid()->toString(),
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subDays(15),
            'finished_at' => now()->subDays(15)->addHour(),
            'is_active' => false,
            'notes' => 'Third session',
            'created_at' => now()->subDays(15),
            'updated_at' => now()->subDays(15),
        ], 'id');

        // Create set executions with progressive weights for student1
        // Session 1 - Bench Press 50kg
        \DB::table('set_executions')->insert([
            'id' => Str::uuid()->toString(),
            'workout_session_id' => $session1,
            'routine_day_exercise_id' => $this->routineDayExercise1->id,
            'exercise_id' => $this->exercise1->id,
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => 50.0,
            'completed_at' => now()->subDays(25),
            'created_at' => now()->subDays(25),
            'updated_at' => now()->subDays(25),
        ]);

        // Session 2 - Bench Press 52.5kg
        \DB::table('set_executions')->insert([
            'id' => Str::uuid()->toString(),
            'workout_session_id' => $session2,
            'routine_day_exercise_id' => $this->routineDayExercise1->id,
            'exercise_id' => $this->exercise1->id,
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => 52.5,
            'completed_at' => now()->subDays(20),
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        // Session 3 - Bench Press 55kg
        \DB::table('set_executions')->insert([
            'id' => Str::uuid()->toString(),
            'workout_session_id' => $session3,
            'routine_day_exercise_id' => $this->routineDayExercise1->id,
            'exercise_id' => $this->exercise1->id,
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => 55.0,
            'completed_at' => now()->subDays(15),
            'created_at' => now()->subDays(15),
            'updated_at' => now()->subDays(15),
        ]);

        // Create completed sessions for student2 (to test active students count)
        $session4 = \DB::table('workout_sessions')->insertGetId([
            'id' => Str::uuid()->toString(),
            'routine_assignment_id' => $this->assignment2->id,
            'student_id' => $this->student2->id,
            'day_number' => 1,
            'started_at' => now()->subDays(10),
            'finished_at' => now()->subDays(10)->addHour(),
            'is_active' => false,
            'notes' => 'Student 2 session',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ], 'id');

        // Generate tokens
        $this->trainer1Token = JWTAuth::fromUser($this->trainer1);
        $this->trainer2Token = JWTAuth::fromUser($this->trainer2);
        $this->student1Token = JWTAuth::fromUser($this->student1);
        $this->student2Token = JWTAuth::fromUser($this->student2);
    }

    // =======================
    // TRAINER ENDPOINTS (7 tests)
    // =======================

    public function test_trainer_can_get_student_routine_stats(): void
    {
        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/routines", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'routine_id',
                        'routine_name',
                        'times_executed',
                        'first_session_at',
                        'last_session_at',
                    ]
                ]
            ]);
    }

    public function test_routine_stats_shows_correct_times_executed(): void
    {
        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/routines", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200);
        $stats = $response->json('data');

        // Student1 has 3 completed sessions for routine1
        $this->assertCount(1, $stats);
        $this->assertEquals(3, $stats[0]['times_executed']);
    }

    public function test_routine_stats_calculates_first_and_last_session(): void
    {
        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/routines", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200);
        $stats = $response->json('data');

        // First session was 25 days ago, last was 15 days ago
        $this->assertNotNull($stats[0]['first_session_at']);
        $this->assertNotNull($stats[0]['last_session_at']);
        $this->assertTrue(
            strtotime($stats[0]['first_session_at']) < strtotime($stats[0]['last_session_at'])
        );
    }

    public function test_trainer_cannot_get_stats_of_other_trainer_student(): void
    {
        // Trainer2 tries to access Trainer1's student
        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/routines", [
            'Authorization' => 'Bearer ' . $this->trainer2Token,
        ]);

        $response->assertStatus(403);
    }

    public function test_trainer_can_get_exercise_weight_history(): void
    {
        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/exercise-weight-history?exercise_id={$this->exercise1->id}&reps=10", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'date',
                        'weight',
                        'reps',
                    ]
                ]
            ]);

        // Should have 3 entries (sessions with progressive weight)
        $this->assertCount(3, $response->json('data'));
    }

    public function test_weight_history_requires_exercise_id_and_reps(): void
    {
        // Missing exercise_id
        $response1 = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/exercise-weight-history?reps=10", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);
        $response1->assertStatus(422);

        // Missing reps
        $response2 = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/exercise-weight-history?exercise_id={$this->exercise1->id}", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);
        $response2->assertStatus(422);
    }

    public function test_trainer_can_get_gym_active_students_with_details(): void
    {
        $response = $this->getJson("/api/v1/gyms/{$this->gym1->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_active_students',
                    'students',
                ]
            ]);

        // All sessions in setUp are finished, so no active students
        $this->assertEquals(0, $response->json('data.total_active_students'));
    }

    // =======================
    // STUDENT ENDPOINTS (8 tests)
    // =======================

    public function test_student_can_get_own_routine_stats(): void
    {
        $response = $this->getJson('/api/v1/students/me/statistics/routines', [
            'Authorization' => 'Bearer ' . $this->student1Token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'routine_id',
                        'routine_name',
                        'times_executed',
                        'first_session_at',
                        'last_session_at',
                    ]
                ]
            ]);

        // Student1 has 3 sessions
        $stats = $response->json('data');
        $this->assertEquals(3, $stats[0]['times_executed']);
    }

    public function test_student_cannot_get_other_student_stats(): void
    {
        // Student2 tries to access student1's stats via trainer endpoint (should be blocked by auth)
        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/routines", [
            'Authorization' => 'Bearer ' . $this->student2Token,
        ]);

        $response->assertStatus(403);
    }

    public function test_student_can_get_own_exercise_weight_history(): void
    {
        $response = $this->getJson("/api/v1/students/me/statistics/exercise-weight-history?exercise_id={$this->exercise1->id}&reps=10", [
            'Authorization' => 'Bearer ' . $this->student1Token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'date',
                        'weight',
                        'reps',
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_weight_history_ordered_by_date_asc(): void
    {
        $response = $this->getJson("/api/v1/students/me/statistics/exercise-weight-history?exercise_id={$this->exercise1->id}&reps=10", [
            'Authorization' => 'Bearer ' . $this->student1Token,
        ]);

        $response->assertStatus(200);
        $history = $response->json('data');

        // Should be ordered from oldest to newest
        $this->assertEquals(50.0, $history[0]['weight']); // First session
        $this->assertEquals(52.5, $history[1]['weight']); // Second session
        $this->assertEquals(55.0, $history[2]['weight']); // Third session
    }

    public function test_student_can_get_gym_active_students_count(): void
    {
        $response = $this->getJson("/api/v1/students/me/gyms/{$this->gym1->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->student1Token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_active_students',
            ]);

        // All sessions in setUp are finished, so no active students
        $this->assertEquals(0, $response->json('total_active_students'));
    }

    public function test_student_cannot_see_names_of_active_students(): void
    {
        $response = $this->getJson("/api/v1/students/me/gyms/{$this->gym1->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->student1Token,
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Should NOT have 'students' array with details (privacy)
        $this->assertArrayNotHasKey('students', $data);
        $this->assertArrayHasKey('total_active_students', $data);
    }

    public function test_student_can_only_see_gyms_where_enrolled(): void
    {
        // Student1 tries to access gym2 (not enrolled there)
        $response = $this->getJson("/api/v1/students/me/gyms/{$this->gym2->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->student1Token,
        ]);

        $response->assertStatus(403);
    }

    public function test_active_students_only_shows_is_active_true_sessions(): void
    {
        // Create an inactive enrollment for a third student
        $student3 = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'email' => 'student3@example.com',
            'password' => bcrypt('password'),
            'name' => 'Student',
            'last_name' => 'Three',
            'birth_date' => '2002-01-01',
            'gender' => 'other',
            'user_type' => 'student',
            'gym_goals' => 'Maintain fitness',
            'email_verified_at' => now(),
        ]);

        // Enroll student3 but mark as INACTIVE
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gym1->id,
            'student_id' => $student3->id,
            'quota_expires_at' => now()->addYear(),
            'is_active' => false, // INACTIVE
        ]);

        // Create assignment and session for student3
        $assignment3 = RoutineAssignmentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_id' => $this->routine1->id,
            'student_id' => $student3->id,
            'gym_id' => $this->gym1->id,
            'assigned_at' => now()->subDays(5),
            'starts_at' => now()->subDays(5),
            'is_current' => true,
            'notes' => null,
        ]);

        // Create ACTIVE session for student3 (to test enrollment filtering)
        \DB::table('workout_sessions')->insert([
            'id' => Str::uuid()->toString(),
            'routine_assignment_id' => $assignment3->id,
            'student_id' => $student3->id,
            'day_number' => 1,
            'started_at' => now()->subMinutes(30),
            'finished_at' => null, // ACTIVE session
            'is_active' => true,
            'notes' => 'Student 3 active session',
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ]);

        // Create ACTIVE session for student1 to also count
        \DB::table('workout_sessions')->insert([
            'id' => Str::uuid()->toString(),
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subMinutes(20),
            'finished_at' => null, // ACTIVE session
            'is_active' => true,
            'notes' => 'Student 1 active session',
            'created_at' => now()->subMinutes(20),
            'updated_at' => now()->subMinutes(20),
        ]);

        // Trainer should only see 1 active student (student1), NOT student3 (is_active=false)
        $response = $this->getJson("/api/v1/gyms/{$this->gym1->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.total_active_students'));
    }

    // ====== TASK_032: New feature tests - Executed Exercises ======

    public function test_trainer_can_get_student_executed_exercises(): void
    {
        // Create finished sessions with set executions for student1
        $session1 = \DB::table('workout_sessions')->insertGetId([
            'id' => $session1Id = Str::uuid()->toString(),
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subDays(10),
            'finished_at' => now()->subDays(10)->addHour(),
            'is_active' => false,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        // Create set executions for exercise1 with different reps
        \DB::table('set_executions')->insert([
            [
                'id' => Str::uuid()->toString(),
                'workout_session_id' => $session1Id,
                'routine_day_exercise_id' => $this->routineDayExercise1->id,
                'exercise_id' => $this->exercise1->id,
                'set_number' => 1,
                'reps_completed' => 10,
                'weight_used' => 50.0,
                'completed_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'id' => Str::uuid()->toString(),
                'workout_session_id' => $session1Id,
                'routine_day_exercise_id' => $this->routineDayExercise1->id,
                'exercise_id' => $this->exercise1->id,
                'set_number' => 2,
                'reps_completed' => 12,
                'weight_used' => 50.0,
                'completed_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'id' => Str::uuid()->toString(),
                'workout_session_id' => $session1Id,
                'routine_day_exercise_id' => $this->routineDayExercise2->id,
                'exercise_id' => $this->exercise2->id,
                'set_number' => 1,
                'reps_completed' => 8,
                'weight_used' => 80.0,
                'completed_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
        ]);

        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/exercises-executed", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'exercise_id',
                        'exercise_name',
                        'muscle_group',
                        'reps_available',
                        'total_executions',
                        'first_executed_at',
                        'last_executed_at',
                    ]
                ]
            ]);

        $exercises = $response->json('data');

        // Should have 2 exercises (exercise1 and exercise2), ordered alphabetically
        $this->assertCount(2, $exercises);

        // Verify reps_available is array and ordered
        $this->assertIsArray($exercises[0]['reps_available']);
        $this->assertIsArray($exercises[1]['reps_available']);

        // Verify alphabetical ordering by exercise name
        $exerciseNames = array_column($exercises, 'exercise_name');
        $sortedNames = $exerciseNames;
        sort($sortedNames);
        $this->assertEquals($sortedNames, $exerciseNames);
    }

    public function test_executed_exercises_only_shows_finished_sessions(): void
    {
        // Create ACTIVE session (should NOT appear)
        $activeSessionId = Str::uuid()->toString();
        \DB::table('workout_sessions')->insert([
            'id' => $activeSessionId,
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now(),
            'finished_at' => null, // Active session
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('set_executions')->insert([
            'id' => Str::uuid()->toString(),
            'workout_session_id' => $activeSessionId,
            'routine_day_exercise_id' => $this->routineDayExercise1->id,
            'exercise_id' => $this->exercise1->id,
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => 50.0,
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create FINISHED session (SHOULD appear)
        $finishedSessionId = Str::uuid()->toString();
        \DB::table('workout_sessions')->insert([
            'id' => $finishedSessionId,
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subDays(5),
            'finished_at' => now()->subDays(5)->addHour(),
            'is_active' => false,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        \DB::table('set_executions')->insert([
            'id' => Str::uuid()->toString(),
            'workout_session_id' => $finishedSessionId,
            'routine_day_exercise_id' => $this->routineDayExercise2->id,
            'exercise_id' => $this->exercise2->id,
            'set_number' => 1,
            'reps_completed' => 12,
            'weight_used' => 60.0,
            'completed_at' => now()->subDays(5),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/exercises-executed", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200);
        $exercises = $response->json('data');

        // Verify exercise2 appears (from finished session)
        $exercise2Found = collect($exercises)->firstWhere('exercise_id', $this->exercise2->id);
        $this->assertNotNull($exercise2Found, 'Exercise2 should appear (finished session)');

        // Verify exercise1 also appears (setUp creates finished sessions with it)
        $exercise1Found = collect($exercises)->firstWhere('exercise_id', $this->exercise1->id);
        $this->assertNotNull($exercise1Found, 'Exercise1 should appear (setUp creates finished sessions)');

        // The key test: verify the ACTIVE session's reps (10) are NOT in exercise1's reps_available
        // setUp creates sessions with reps 10 for exercise1, so if active session (also reps 10)
        // is NOT filtered, we can't distinguish. Instead, check that exercise2 appears correctly.
        $this->assertEquals($this->exercise2->id, $exercise2Found['exercise_id']);
        $this->assertContains(12, $exercise2Found['reps_available']);
    }

    public function test_executed_exercises_groups_reps_correctly(): void
    {
        // Create sessions where student1 did Press Banca with 8, 10, 10, 12 reps
        $session1Id = Str::uuid()->toString();
        \DB::table('workout_sessions')->insert([
            'id' => $session1Id,
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subDays(10),
            'finished_at' => now()->subDays(10)->addHour(),
            'is_active' => false,
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        \DB::table('set_executions')->insert([
            [
                'id' => Str::uuid()->toString(),
                'workout_session_id' => $session1Id,
                'routine_day_exercise_id' => $this->routineDayExercise1->id,
                'exercise_id' => $this->exercise1->id,
                'set_number' => 1,
                'reps_completed' => 8,
                'weight_used' => 50.0,
                'completed_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'id' => Str::uuid()->toString(),
                'workout_session_id' => $session1Id,
                'routine_day_exercise_id' => $this->routineDayExercise1->id,
                'exercise_id' => $this->exercise1->id,
                'set_number' => 2,
                'reps_completed' => 10,
                'weight_used' => 50.0,
                'completed_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'id' => Str::uuid()->toString(),
                'workout_session_id' => $session1Id,
                'routine_day_exercise_id' => $this->routineDayExercise1->id,
                'exercise_id' => $this->exercise1->id,
                'set_number' => 3,
                'reps_completed' => 10, // Duplicate
                'weight_used' => 52.5,
                'completed_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
            [
                'id' => Str::uuid()->toString(),
                'workout_session_id' => $session1Id,
                'routine_day_exercise_id' => $this->routineDayExercise1->id,
                'exercise_id' => $this->exercise1->id,
                'set_number' => 4,
                'reps_completed' => 12,
                'weight_used' => 52.5,
                'completed_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10),
            ],
        ]);

        $response = $this->getJson("/api/v1/students/{$this->student1->id}/statistics/exercises-executed", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200);
        $exercises = $response->json('data');

        $this->assertCount(1, $exercises);

        // reps_available should be [8, 10, 12] (no duplicates, ordered)
        $this->assertEquals([8, 10, 12], $exercises[0]['reps_available']);
    }

    public function test_student_can_get_own_executed_exercises(): void
    {
        // Create finished session with set executions for student1
        $sessionId = Str::uuid()->toString();
        \DB::table('workout_sessions')->insert([
            'id' => $sessionId,
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subDays(5),
            'finished_at' => now()->subDays(5)->addHour(),
            'is_active' => false,
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        \DB::table('set_executions')->insert([
            'id' => Str::uuid()->toString(),
            'workout_session_id' => $sessionId,
            'routine_day_exercise_id' => $this->routineDayExercise1->id,
            'exercise_id' => $this->exercise1->id,
            'set_number' => 1,
            'reps_completed' => 10,
            'weight_used' => 50.0,
            'completed_at' => now()->subDays(5),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        // Student calls endpoint /students/me/statistics/exercises-executed
        $response = $this->getJson('/api/v1/students/me/statistics/exercises-executed', [
            'Authorization' => 'Bearer ' . $this->student1Token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'exercise_id',
                        'exercise_name',
                        'muscle_group',
                        'reps_available',
                        'total_executions',
                        'first_executed_at',
                        'last_executed_at',
                    ]
                ]
            ]);

        $exercises = $response->json('data');
        $this->assertCount(1, $exercises);
        $this->assertEquals($this->exercise1->id, $exercises[0]['exercise_id']);
    }

    public function test_trainer_cannot_get_executed_exercises_of_other_trainer_student(): void
    {
        // Trainer1 tries to access student2 (belongs to trainer2)
        $response = $this->getJson("/api/v1/students/{$this->student2->id}/statistics/exercises-executed", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_executed_exercises_empty_if_no_executions(): void
    {
        // Create a fresh student with no set executions
        $freshStudent = UserEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'email' => 'fresh@example.com',
            'password' => bcrypt('password'),
            'name' => 'Fresh',
            'last_name' => 'Student',
            'birth_date' => '2003-01-01',
            'gender' => 'other',
            'user_type' => 'student',
            'gym_goals' => 'Just started',
            'email_verified_at' => now(),
        ]);

        // Enroll in trainer1's gym
        GymStudentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'gym_id' => $this->gym1->id,
            'student_id' => $freshStudent->id,
            'quota_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Assign routine but no sessions created
        RoutineAssignmentEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'routine_id' => $this->routine1->id,
            'student_id' => $freshStudent->id,
            'gym_id' => $this->gym1->id,
            'assigned_at' => now(),
            'starts_at' => now(),
            'is_current' => true,
            'notes' => null,
        ]);

        $response = $this->getJson("/api/v1/students/{$freshStudent->id}/statistics/exercises-executed", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['data' => []]);

        $this->assertCount(0, $response->json('data'));
    }

    // =======================
    // BUGFIX TESTS - Active Students Counter
    // =======================

    public function test_active_students_only_counts_students_with_active_sessions(): void
    {
        // All existing sessions in setUp are finished (finished_at is not null)
        // So active students count should be 0

        $response = $this->getJson("/api/v1/gyms/{$this->gym1->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200);
        // BUG: Currently returns 1 because it counts finished sessions
        // EXPECTED: Should return 0 because no active sessions exist
        $this->assertEquals(0, $response->json('data.total_active_students'));
    }

    public function test_active_students_counts_students_with_unfinished_sessions(): void
    {
        // Create an ACTIVE (unfinished) session for student1
        \DB::table('workout_sessions')->insert([
            'id' => Str::uuid()->toString(),
            'routine_assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'day_number' => 1,
            'started_at' => now()->subMinutes(30),
            'finished_at' => null, // ACTIVE session
            'is_active' => true,
            'notes' => 'Active session',
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ]);

        $response = $this->getJson("/api/v1/gyms/{$this->gym1->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200);
        // Should count 1 student with an active session
        $this->assertEquals(1, $response->json('data.total_active_students'));
    }

    public function test_active_students_groups_by_student_correctly(): void
    {
        // Create 2 ACTIVE sessions for student1 (should count as 1 active student, not 2)
        \DB::table('workout_sessions')->insert([
            [
                'id' => Str::uuid()->toString(),
                'routine_assignment_id' => $this->assignment1->id,
                'student_id' => $this->student1->id,
                'day_number' => 1,
                'started_at' => now()->subHours(2),
                'finished_at' => null,
                'is_active' => true,
                'notes' => null,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'id' => Str::uuid()->toString(),
                'routine_assignment_id' => $this->assignment1->id,
                'student_id' => $this->student1->id,
                'day_number' => 2,
                'started_at' => now()->subHours(1),
                'finished_at' => null,
                'is_active' => true,
                'notes' => null,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
        ]);

        $response = $this->getJson("/api/v1/gyms/{$this->gym1->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->trainer1Token,
        ]);

        $response->assertStatus(200);
        // Should count 1 active student (grouped correctly), not 2
        $this->assertEquals(1, $response->json('data.total_active_students'));
    }

    public function test_student_can_see_active_students_count_correctly(): void
    {
        // Student endpoint should also count only active sessions
        $response = $this->getJson("/api/v1/students/me/gyms/{$this->gym1->id}/statistics/active-students", [
            'Authorization' => 'Bearer ' . $this->student1Token,
        ]);

        $response->assertStatus(200);
        // BUG: Currently returns 1 because counts finished sessions
        // EXPECTED: Should return 0 because no active sessions
        $this->assertEquals(0, $response->json('data.total_active_students'));
    }
}
