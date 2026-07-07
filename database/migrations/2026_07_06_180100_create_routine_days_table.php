<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutineDaysTable extends Migration
{
    public function up()
    {
        Schema::create('routine_days', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('routine_id');
            $table->integer('day_number');
            $table->string('name', 255);
            $table->timestamps();

            $table->foreign('routine_id')->references('id')->on('routines')->onDelete('cascade');
            $table->unique(['routine_id', 'day_number']);
            $table->index('routine_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('routine_days');
    }
}
