<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MuscleGroupSeeder extends Seeder
{
    public function run(): void
    {
        $muscleGroups = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Pecho (Pectorales)',
                'description' => 'Músculos pectorales mayores y menores. Responsables de la aducción y rotación interna del brazo.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Espalda alta (Trapecio)',
                'description' => 'Trapecio superior, medio e inferior. Responsable de la elevación, retracción y rotación de la escápula.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Espalda media (Romboides)',
                'description' => 'Romboides mayor y menor. Retracción de las escápulas y estabilización de la cintura escapular.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Dorsales (Dorsal ancho)',
                'description' => 'Latissimus dorsi. Responsable de la extensión, aducción y rotación interna del hombro.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Hombros - Deltoides anterior',
                'description' => 'Porción frontal del deltoides. Responsable de la flexión y rotación interna del hombro.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Hombros - Deltoides lateral',
                'description' => 'Porción media del deltoides. Responsable de la abducción del hombro.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Hombros - Deltoides posterior',
                'description' => 'Porción trasera del deltoides. Responsable de la extensión y rotación externa del hombro.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Bíceps',
                'description' => 'Bíceps braquial y braquial anterior. Flexión del codo y supinación del antebrazo.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Tríceps',
                'description' => 'Tríceps braquial (cabeza larga, lateral y medial). Extensión del codo.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Antebrazos',
                'description' => 'Flexores y extensores del antebrazo. Control de muñeca, dedos y agarre.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Abdominales (Recto abdominal)',
                'description' => 'Recto del abdomen. Flexión de la columna vertebral y estabilización del core.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Oblicuos',
                'description' => 'Oblicuos externos e internos. Rotación del torso y flexión lateral.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Cuádriceps',
                'description' => 'Vasto lateral, vasto medial, vasto intermedio y recto femoral. Extensión de rodilla.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Isquiotibiales',
                'description' => 'Bíceps femoral, semitendinoso y semimembranoso. Flexión de rodilla y extensión de cadera.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Gemelos (Pantorrillas)',
                'description' => 'Gastrocnemio y sóleo. Flexión plantar del tobillo.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Glúteos',
                'description' => 'Glúteo mayor, medio y menor. Extensión, abducción y rotación de la cadera.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('muscle_groups')->insert($muscleGroups);
    }
}
