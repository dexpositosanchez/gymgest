<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToGymStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gym_students', function (Blueprint $table) {
            // Composite index for active students lookup queries
            // Used by GetGymActiveStudentsUseCase and GetGymActiveStudentsCountUseCase
            $table->index(['gym_id', 'is_active', 'student_id'], 'idx_gym_students_active_lookup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gym_students', function (Blueprint $table) {
            $table->dropIndex('idx_gym_students_active_lookup');
        });
    }
}
