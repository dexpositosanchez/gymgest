<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutineDayExercisesTable extends Migration
{
    public function up()
    {
        Schema::create('routine_day_exercises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('routine_day_id');
            $table->uuid('exercise_id');
            $table->integer('order_index');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('routine_day_id')->references('id')->on('routine_days')->onDelete('cascade');
            $table->foreign('exercise_id')->references('id')->on('exercises');
            $table->unique(['routine_day_id', 'order_index']);
            $table->index('routine_day_id');
            $table->index('exercise_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('routine_day_exercises');
    }
}
