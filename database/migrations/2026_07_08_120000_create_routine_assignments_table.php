<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutineAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('routine_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('routine_id');
            $table->uuid('student_id');
            $table->uuid('gym_id');
            $table->timestamp('assigned_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->date('starts_at');
            $table->boolean('is_current')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('routine_id')->references('id')->on('routines')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('gym_id')->references('id')->on('gyms')->onDelete('cascade');

            // Unique constraint: no duplicate assignments (routine + student + gym)
            $table->unique(['routine_id', 'student_id', 'gym_id'], 'routine_student_gym_unique');

            // Index for fast lookup of current routine per student/gym
            $table->index(['student_id', 'gym_id', 'is_current'], 'student_gym_current_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('routine_assignments');
    }
}
