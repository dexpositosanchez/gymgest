<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use App\Infrastructure\Persistence\Eloquent\MuscleGroupEloquentModel;
use App\Infrastructure\Persistence\Eloquent\ExerciseEloquentModel;

class RoutineManagementTest extends TestCase
{
    use RefreshDatabase;

    private $trainer;
    private $token;
    private $exercise1;
    private $exercise2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create trainer user
        $this->trainer = UserEloquentModel::create([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'name' => 'Trainer',
            'last_name' => 'Test',
            'email' => 'trainer@test.com',
            'password' => bcrypt('password123'),
            'user_type' => 'trainer',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'email_verified_at' => now(),
        ]);

        $this->token = auth()->login($this->trainer);

        // Create muscle group and exercises for testing
        $muscleGroup = MuscleGroupEloquentModel::create([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'name' => 'Pecho',
            'description' => 'Músculos del pecho',
        ]);

        $this->exercise1 = ExerciseEloquentModel::create([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'name' => 'Press banca',
            'description' => 'Ejercicio para pecho',
            'muscle_group_id' => $muscleGroup->id,
            'is_default' => true,
            'trainer_id' => null,
        ]);

        $this->exercise2 = ExerciseEloquentModel::create([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'name' => 'Flexiones',
            'description' => 'Ejercicio para pecho',
            'muscle_group_id' => $muscleGroup->id,
            'is_default' => true,
            'trainer_id' => null,
        ]);
    }

    private function createSets(int $numberOfSets, int $reps): array
    {
        $sets = [];
        for ($i = 1; $i <= $numberOfSets; $i++) {
            $sets[] = [
                'set_number' => $i,
                'reps' => $reps,
                'notes' => null,
            ];
        }
        return $sets;
    }

    public function test_can_create_routine_with_nested_days_and_exercises()
    {
        $payload = [
            'name' => 'Full Body Beginner',
            'description' => 'Rutina de cuerpo completo para principiantes',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1 - Pecho',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => [
                                ['set_number' => 1, 'reps' => 12, 'notes' => null],
                                ['set_number' => 2, 'reps' => 10, 'notes' => null],
                                ['set_number' => 3, 'reps' => 8, 'notes' => null],
                            ],
                            'notes' => 'Mantener espalda firme',
                        ],
                        [
                            'exercise_id' => $this->exercise2->id,
                            'order_index' => 1,
                            'sets' => [
                                ['set_number' => 1, 'reps' => 15, 'notes' => null],
                                ['set_number' => 2, 'reps' => 15, 'notes' => null],
                                ['set_number' => 3, 'reps' => 15, 'notes' => null],
                            ],
                            'notes' => null,
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'difficulty',
                ],
            ]);
    }

    public function test_can_list_routines()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/routines');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_filter_routines_by_difficulty()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/routines?difficulty=beginner');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_routine_details()
    {
        // Create routine first
        $payload = [
            'name' => 'Test Routine',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => $this->createSets(3, 12),
                        ],
                    ],
                ],
            ],
        ];

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $routineId = $createResponse->json('data.id');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/routines/{$routineId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'difficulty',
                    'days' => [
                        '*' => [
                            'day_number',
                            'name',
                            'exercises' => [
                                '*' => [
                                    'exercise_id',
                                    'sets' => [
                                        '*' => [
                                            'set_number',
                                            'reps',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_can_update_routine()
    {
        // Create routine first
        $payload = [
            'name' => 'Original Routine',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => $this->createSets(3, 12),
                        ],
                    ],
                ],
            ],
        ];

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $routineId = $createResponse->json('data.id');

        // Update routine
        $updatePayload = [
            'name' => 'Updated Routine',
            'difficulty' => 'advanced',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1 - Updated',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => $this->createSets(4, 10, 90),
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/routines/{$routineId}", $updatePayload);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Routine',
                    'difficulty' => 'advanced',
                ],
            ]);
    }

    public function test_cannot_update_routine_of_another_trainer()
    {
        // Create routine with first trainer
        $payload = [
            'name' => 'Routine Trainer 1',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => $this->createSets(3, 12),
                        ],
                    ],
                ],
            ],
        ];

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $routineId = $createResponse->json('data.id');

        // Create another trainer
        $otherTrainer = UserEloquentModel::create([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'name' => 'Other',
            'last_name' => 'Trainer',
            'email' => 'other@test.com',
            'password' => bcrypt('password123'),
            'user_type' => 'trainer',
            'gender' => 'female',
            'birth_date' => '1992-01-01',
            'email_verified_at' => now(),
        ]);

        $otherToken = auth()->login($otherTrainer);

        // Try to update with other trainer
        $response = $this->withHeader('Authorization', 'Bearer ' . $otherToken)
            ->putJson("/api/v1/routines/{$routineId}", $payload);

        $response->assertStatus(403);
    }

    public function test_can_delete_routine()
    {
        // Create routine first
        $payload = [
            'name' => 'Routine to Delete',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => $this->createSets(3, 12),
                        ],
                    ],
                ],
            ],
        ];

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $routineId = $createResponse->json('data.id');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/routines/{$routineId}");

        $response->assertStatus(204);
    }

    public function test_cannot_delete_routine_of_another_trainer()
    {
        // Create routine with first trainer
        $payload = [
            'name' => 'Routine Trainer 1',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => $this->createSets(3, 12),
                        ],
                    ],
                ],
            ],
        ];

        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $routineId = $createResponse->json('data.id');

        // Create another trainer
        $otherTrainer = UserEloquentModel::create([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'name' => 'Other',
            'last_name' => 'Trainer2',
            'email' => 'other2@test.com',
            'password' => bcrypt('password123'),
            'user_type' => 'trainer',
            'gender' => 'male',
            'birth_date' => '1993-01-01',
            'email_verified_at' => now(),
        ]);

        $otherToken = auth()->login($otherTrainer);

        // Try to delete with other trainer
        $response = $this->withHeader('Authorization', 'Bearer ' . $otherToken)
            ->deleteJson("/api/v1/routines/{$routineId}");

        $response->assertStatus(403);
    }

    public function test_validation_requires_at_least_one_day()
    {
        $payload = [
            'name' => 'Invalid Routine',
            'difficulty' => 'beginner',
            'days' => [],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $response->assertStatus(422);
    }

    public function test_validation_requires_at_least_one_exercise_per_day()
    {
        $payload = [
            'name' => 'Invalid Routine',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1',
                    'exercises' => [],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $response->assertStatus(422);
    }

    public function test_validation_rejects_invalid_day_number()
    {
        $payload = [
            'name' => 'Invalid Routine',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 8,
                    'name' => 'Día 8',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => $this->createSets(3, 12),
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $response->assertStatus(422);
    }

    public function test_validation_rejects_zero_sets()
    {
        $payload = [
            'name' => 'Invalid Routine',
            'difficulty' => 'beginner',
            'days' => [
                [
                    'day_number' => 1,
                    'name' => 'Día 1',
                    'exercises' => [
                        [
                            'exercise_id' => $this->exercise1->id,
                            'order_index' => 0,
                            'sets' => [], // Empty array - no sets
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/routines', $payload);

        $response->assertStatus(422);
    }
}
