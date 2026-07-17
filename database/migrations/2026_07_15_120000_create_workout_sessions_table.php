<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkoutSessionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('routine_assignment_id');
            $table->uuid('student_id');
            $table->integer('day_number');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('routine_assignment_id')
                ->references('id')
                ->on('routine_assignments')
                ->onDelete('cascade');

            $table->foreign('student_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes
            $table->index('student_id');
            $table->index('is_active');
            $table->index(['student_id', 'is_active']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
}
