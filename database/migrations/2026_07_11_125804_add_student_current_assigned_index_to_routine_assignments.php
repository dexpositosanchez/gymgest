<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudentCurrentAssignedIndexToRoutineAssignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('routine_assignments', function (Blueprint $table) {
            // Add index for student routine list queries (ordered by is_current DESC, assigned_at DESC)
            $table->index(['student_id', 'is_current', 'assigned_at'], 'student_current_assigned_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('routine_assignments', function (Blueprint $table) {
            $table->dropIndex('student_current_assigned_idx');
        });
    }
}
