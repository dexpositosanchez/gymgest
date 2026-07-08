<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Infrastructure\Persistence\Eloquent\UserEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymEloquentModel;
use App\Infrastructure\Persistence\Eloquent\GymStudentEloquentModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Datos de desarrollo: 10 trainers, 13 gyms, 30 students, 15 asignaciones
     */
    public function run(): void
    {
        echo "🌱 Cargando datos de desarrollo...\n";

        // Password común para todos los usuarios
        $password = Hash::make('Password123!');
        $now = now();

        // ===== ENTRENADORES (10) =====
        echo "👨‍🏫 Creando 10 entrenadores...\n";

        $trainers = [];
        for ($i = 1; $i <= 10; $i++) {
            $trainers[$i] = UserEloquentModel::firstOrCreate(
                ['email' => "trainer{$i}@gymgest.dev"],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => "Trainer",
                    'last_name' => "Number {$i}",
                    'password' => $password,
                    'user_type' => 'trainer',
                    'birth_date' => '1985-01-0' . (($i % 9) + 1),
                    'gender' => ['male', 'female', 'other'][($i - 1) % 3],
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // ===== GIMNASIOS (13) =====
        echo "🏋️ Creando 13 gimnasios...\n";

        $gyms = [];
        $gymConfig = [
            // 7 trainers con 1 gym cada uno (trainers 1-7)
            1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 1,
            // 2 trainers con 2 gyms cada uno (trainers 8-9)
            8 => 2, 9 => 2,
            // 1 trainer con 3 gyms (trainer 10)
            10 => 3,
        ];

        $gymIndex = 1;
        foreach ($gymConfig as $trainerId => $gymCount) {
            for ($g = 1; $g <= $gymCount; $g++) {
                $gyms[$gymIndex] = GymEloquentModel::firstOrCreate(
                    ['name' => "Gym {$gymIndex} - Trainer{$trainerId}"],
                    [
                        'id' => Str::uuid()->toString(),
                        'trainer_id' => $trainers[$trainerId]->id,
                        'address' => "Calle Fitness {$gymIndex}, {$gymIndex}",
                        'locality' => ["Madrid", "Barcelona", "Valencia", "Sevilla"][$gymIndex % 4],
                        'province' => ["Madrid", "Barcelona", "Valencia", "Sevilla"][$gymIndex % 4],
                        'country' => 'España',
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
                $gymIndex++;
            }
        }

        // ===== ALUMNOS (30) =====
        echo "👥 Creando 30 alumnos...\n";

        $students = [];
        for ($i = 1; $i <= 30; $i++) {
            $students[$i] = UserEloquentModel::firstOrCreate(
                ['email' => "student{$i}@gymgest.dev"],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => "Student",
                    'last_name' => "Number {$i}",
                    'password' => $password,
                    'user_type' => 'student',
                    'birth_date' => '1995-0' . (($i % 9) + 1) . '-15',
                    'gender' => ['male', 'female', 'other'][($i - 1) % 3],
                    'gym_goals' => 'Mejorar condición física',
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // ===== ASIGNACIONES (15 alumnos asignados, 15 sin asignar) =====
        echo "📋 Creando 15 asignaciones con diferentes estados...\n";

        // 5 alumnos con cuota vigente (>30 días)
        for ($i = 1; $i <= 5; $i++) {
            GymStudentEloquentModel::firstOrCreate(
                [
                    'gym_id' => $gyms[($i % 13) + 1]->id,
                    'student_id' => $students[$i]->id,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'quota_expires_at' => $now->copy()->addDays(30 + $i),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 5 alumnos con cuota próxima a caducar (1-7 días)
        for ($i = 6; $i <= 10; $i++) {
            GymStudentEloquentModel::firstOrCreate(
                [
                    'gym_id' => $gyms[(($i - 1) % 13) + 1]->id,
                    'student_id' => $students[$i]->id,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'quota_expires_at' => $now->copy()->addDays(($i - 5)), // 1-5 días
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 3 alumnos con cuota caducada (fecha pasada)
        for ($i = 11; $i <= 13; $i++) {
            GymStudentEloquentModel::firstOrCreate(
                [
                    'gym_id' => $gyms[(($i - 1) % 13) + 1]->id,
                    'student_id' => $students[$i]->id,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'quota_expires_at' => $now->copy()->subDays(($i - 10)), // -1, -2, -3 días
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 2 alumnos inactivos (is_active = false)
        for ($i = 14; $i <= 15; $i++) {
            GymStudentEloquentModel::firstOrCreate(
                [
                    'gym_id' => $gyms[(($i - 1) % 13) + 1]->id,
                    'student_id' => $students[$i]->id,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'quota_expires_at' => $now->copy()->subDays(30),
                    'is_active' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        echo "✅ Datos de desarrollo cargados exitosamente:\n";
        echo "   - 10 entrenadores (trainer1@gymgest.dev ... trainer10@gymgest.dev)\n";
        echo "   - 13 gimnasios distribuidos entre los entrenadores\n";
        echo "   - 30 alumnos (student1@gymgest.dev ... student30@gymgest.dev)\n";
        echo "   - 15 asignaciones:\n";
        echo "     * 5 con cuota vigente (>30 días)\n";
        echo "     * 5 con cuota próxima a caducar (1-7 días)\n";
        echo "     * 3 con cuota caducada\n";
        echo "     * 2 inactivos\n";
        echo "   - Password para todos: Password123!\n\n";
    }
}
