<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutineDayExerciseSetsTable extends Migration
{
    public function up()
    {
        Schema::create('routine_day_exercise_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('routine_day_exercise_id');
            $table->integer('set_number');
            $table->integer('reps');
            $table->integer('rest_seconds');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('routine_day_exercise_id')->references('id')->on('routine_day_exercises')->onDelete('cascade');
            $table->unique(['routine_day_exercise_id', 'set_number']);
            $table->index('routine_day_exercise_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('routine_day_exercise_sets');
    }
}
