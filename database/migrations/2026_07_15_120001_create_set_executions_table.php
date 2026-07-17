<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetExecutionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('set_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workout_session_id');
            $table->uuid('routine_day_exercise_id');
            $table->uuid('exercise_id');
            $table->integer('set_number');
            $table->integer('reps_completed');
            $table->decimal('weight_used', 5, 2)->nullable();
            $table->timestamp('completed_at');
            $table->timestamps();

            // Foreign keys
            $table->foreign('workout_session_id')
                ->references('id')
                ->on('workout_sessions')
                ->onDelete('cascade');

            $table->foreign('routine_day_exercise_id')
                ->references('id')
                ->on('routine_day_exercises')
                ->onDelete('cascade');

            $table->foreign('exercise_id')
                ->references('id')
                ->on('exercises')
                ->onDelete('cascade');

            // Indexes
            $table->index('workout_session_id');
            $table->index('exercise_id');
            $table->index(['workout_session_id', 'exercise_id']);
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('set_executions');
    }
}
