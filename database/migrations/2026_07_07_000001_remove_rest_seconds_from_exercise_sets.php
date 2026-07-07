<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveRestSecondsFromExerciseSets extends Migration
{
    public function up()
    {
        Schema::table('routine_day_exercise_sets', function (Blueprint $table) {
            $table->dropColumn('rest_seconds');
        });
    }

    public function down()
    {
        Schema::table('routine_day_exercise_sets', function (Blueprint $table) {
            $table->integer('rest_seconds')->after('reps');
        });
    }
}
