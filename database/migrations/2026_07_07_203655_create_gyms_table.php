<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGymsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gyms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('trainer_id');
            $table->string('name', 255);
            $table->string('address', 255);
            $table->string('city', 100);
            $table->string('postal_code', 20);
            $table->string('country', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('trainer_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('trainer_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gyms');
    }
}
