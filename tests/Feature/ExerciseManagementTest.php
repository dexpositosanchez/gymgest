<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\ExerciseEloquentModel;
use App\Infrastructure\Persistence\Eloquent\MuscleGroupEloquentModel;
use App\Infrastructure\Persistence\Eloquent\TrainerExercisePreferenceEloquentModel;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExerciseManagementTest extends TestCase
{
    use RefreshDatabase;

    private string $trainerId;
    private string $otherTrainerId;
    private string $muscleGroupId;
    private string $defaultExerciseId;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create muscle group
        $this->muscleGroupId = Str::uuid()->toString();
        MuscleGroupEloquentModel::create([
            'id' => $this->muscleGroupId,
            'name' => 'Pecho',
            'description' => 'Músculos pectorales',
        ]);

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

        // Create default exercise
        $this->defaultExerciseId = Str::uuid()->toString();
        ExerciseEloquentModel::create([
            'id' => $this->defaultExerciseId,
            'name' => 'Press de banca',
            'description' => 'Ejercicio fundamental para el pecho',
            'muscle_group_id' => $this->muscleGroupId,
            'trainer_id' => null,
            'is_default' => true,
        ]);
    }

    public function test_trainer_can_list_all_exercises(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/exercises');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'muscle_group',
                        'type',
                        'is_active',
                    ],
                ],
            ]);
    }

    public function test_trainer_can_filter_exercises_by_muscle_group(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/exercises?muscle_group_id=' . $this->muscleGroupId);

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $exercise) {
            $this->assertEquals($this->muscleGroupId, $exercise['muscle_group']['id']);
        }
    }

    public function test_trainer_can_search_exercises_by_name(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/exercises?search=Press');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $exercise) {
            $this->assertStringContainsStringIgnoringCase('Press', $exercise['name']);
        }
    }

    public function test_trainer_can_view_single_exercise(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/exercises/' . $this->defaultExerciseId);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'muscle_group',
                    'type',
                    'is_active',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->defaultExerciseId,
                    'name' => 'Press de banca',
                ],
            ]);
    }

    public function test_trainer_can_create_custom_exercise(): void
    {
        $exerciseData = [
            'name' => 'Mi ejercicio custom',
            'description' => 'Descripción de mi ejercicio personalizado',
            'muscle_group_id' => $this->muscleGroupId,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/exercises', $exerciseData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'type',
                ],
            ])
            ->assertJson([
                'data' => [
                    'name' => 'Mi ejercicio custom',
                    'type' => 'custom',
                ],
            ]);

        $this->assertDatabaseHas('exercises', [
            'name' => 'Mi ejercicio custom',
            'trainer_id' => $this->trainerId,
            'is_default' => false,
        ]);
    }

    public function test_trainer_can_update_own_custom_exercise(): void
    {
        // Create custom exercise
        $exerciseId = Str::uuid()->toString();
        ExerciseEloquentModel::create([
            'id' => $exerciseId,
            'name' => 'Original name',
            'description' => 'Original description for exercise',
            'muscle_group_id' => $this->muscleGroupId,
            'trainer_id' => $this->trainerId,
            'is_default' => false,
        ]);

        $updateData = [
            'name' => 'Updated name',
            'description' => 'Updated description for exercise',
            'muscle_group_id' => $this->muscleGroupId,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/exercises/' . $exerciseId, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated name',
                ],
            ]);

        $this->assertDatabaseHas('exercises', [
            'id' => $exerciseId,
            'name' => 'Updated name',
        ]);
    }

    public function test_trainer_cannot_update_default_exercise(): void
    {
        $updateData = [
            'name' => 'Trying to update',
            'description' => 'This should fail because exercise is default',
            'muscle_group_id' => $this->muscleGroupId,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/exercises/' . $this->defaultExerciseId, $updateData);

        $response->assertStatus(403);
    }

    public function test_trainer_cannot_update_other_trainers_exercise(): void
    {
        // Create exercise for other trainer
        $exerciseId = Str::uuid()->toString();
        ExerciseEloquentModel::create([
            'id' => $exerciseId,
            'name' => 'Other trainer exercise',
            'description' => 'This belongs to another trainer',
            'muscle_group_id' => $this->muscleGroupId,
            'trainer_id' => $this->otherTrainerId,
            'is_default' => false,
        ]);

        $updateData = [
            'name' => 'Trying to update',
            'description' => 'This should fail because belongs to other trainer',
            'muscle_group_id' => $this->muscleGroupId,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/exercises/' . $exerciseId, $updateData);

        $response->assertStatus(403);
    }

    public function test_trainer_can_delete_own_custom_exercise(): void
    {
        // Create custom exercise
        $exerciseId = Str::uuid()->toString();
        ExerciseEloquentModel::create([
            'id' => $exerciseId,
            'name' => 'Exercise to delete',
            'description' => 'This exercise will be deleted',
            'muscle_group_id' => $this->muscleGroupId,
            'trainer_id' => $this->trainerId,
            'is_default' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/exercises/' . $exerciseId);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('exercises', [
            'id' => $exerciseId,
        ]);
    }

    public function test_trainer_cannot_delete_default_exercise(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/exercises/' . $this->defaultExerciseId);

        $response->assertStatus(403);

        $this->assertDatabaseHas('exercises', [
            'id' => $this->defaultExerciseId,
        ]);
    }

    public function test_trainer_cannot_delete_other_trainers_exercise(): void
    {
        // Create exercise for other trainer
        $exerciseId = Str::uuid()->toString();
        ExerciseEloquentModel::create([
            'id' => $exerciseId,
            'name' => 'Other trainer exercise',
            'description' => 'This belongs to another trainer',
            'muscle_group_id' => $this->muscleGroupId,
            'trainer_id' => $this->otherTrainerId,
            'is_default' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/exercises/' . $exerciseId);

        $response->assertStatus(403);

        $this->assertDatabaseHas('exercises', [
            'id' => $exerciseId,
        ]);
    }

    public function test_trainer_can_deactivate_default_exercise(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/exercises/' . $this->defaultExerciseId . '/toggle', [
                'is_active' => false,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('trainer_exercise_preferences', [
            'trainer_id' => $this->trainerId,
            'exercise_id' => $this->defaultExerciseId,
            'is_active' => false,
        ]);
    }

    public function test_trainer_can_reactivate_default_exercise(): void
    {
        // First deactivate
        TrainerExercisePreferenceEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'exercise_id' => $this->defaultExerciseId,
            'is_active' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/exercises/' . $this->defaultExerciseId . '/toggle', [
                'is_active' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('trainer_exercise_preferences', [
            'trainer_id' => $this->trainerId,
            'exercise_id' => $this->defaultExerciseId,
            'is_active' => true,
        ]);
    }

    public function test_trainer_can_list_muscle_groups(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/muscle-groups');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                    ],
                ],
            ]);
    }

    public function test_deactivated_exercises_are_not_listed_by_default(): void
    {
        // Deactivate exercise
        TrainerExercisePreferenceEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'exercise_id' => $this->defaultExerciseId,
            'is_active' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/exercises');

        $data = $response->json('data');
        $exerciseIds = array_column($data, 'id');

        $this->assertNotContains($this->defaultExerciseId, $exerciseIds);
    }

    public function test_deactivated_exercises_are_listed_when_include_inactive_is_true(): void
    {
        // Deactivate exercise
        TrainerExercisePreferenceEloquentModel::create([
            'id' => Str::uuid()->toString(),
            'trainer_id' => $this->trainerId,
            'exercise_id' => $this->defaultExerciseId,
            'is_active' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/exercises?include_inactive=true');

        $data = $response->json('data');
        $exerciseIds = array_column($data, 'id');

        $this->assertContains($this->defaultExerciseId, $exerciseIds);
    }
}
