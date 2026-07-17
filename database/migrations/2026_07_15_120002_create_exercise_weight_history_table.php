<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExerciseWeightHistoryTable extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_weight_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('exercise_id');
            $table->integer('reps');
            $table->decimal('weight', 5, 2);
            $table->timestamp('last_used_at');
            $table->timestamps();

            // Foreign keys
            $table->foreign('student_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('exercise_id')
                ->references('id')
                ->on('exercises')
                ->onDelete('cascade');

            // Unique constraint: one record per student + exercise + reps combination
            $table->unique(['student_id', 'exercise_id', 'reps']);

            // Indexes
            $table->index('student_id');
            $table->index('exercise_id');
            $table->index(['student_id', 'exercise_id']);
            $table->index('last_used_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_weight_history');
    }
}
