<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGymStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gym_students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('gym_id');
            $table->uuid('student_id');
            $table->date('quota_expires_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('gym_id')
                ->references('id')
                ->on('gyms')
                ->onDelete('cascade');

            $table->foreign('student_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unique(['gym_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gym_students');
    }
}
