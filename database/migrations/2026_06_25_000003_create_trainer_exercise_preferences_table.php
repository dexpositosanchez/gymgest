<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainerExercisePreferencesTable extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_exercise_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->uuid('exercise_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('trainer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('exercise_id')
                  ->references('id')
                  ->on('exercises')
                  ->onDelete('cascade');

            // Unique constraint
            $table->unique(['trainer_id', 'exercise_id']);

            // Indexes
            $table->index('trainer_id');
            $table->index('exercise_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_exercise_preferences');
    }
}
