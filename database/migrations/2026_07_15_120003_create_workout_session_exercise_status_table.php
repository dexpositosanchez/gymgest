<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkoutSessionExerciseStatusTable extends Migration
{
    public function up(): void
    {
        Schema::create('workout_session_exercise_status', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workout_session_id');
            $table->uuid('exercise_id');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('workout_session_id')
                ->references('id')
                ->on('workout_sessions')
                ->onDelete('cascade');

            $table->foreign('exercise_id')
                ->references('id')
                ->on('exercises')
                ->onDelete('cascade');

            // Unique constraint: one status per session + exercise
            $table->unique(['workout_session_id', 'exercise_id']);

            // Indexes
            $table->index('workout_session_id');
            $table->index('exercise_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_session_exercise_status');
    }
}
