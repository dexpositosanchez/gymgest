<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExercisesTable extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->text('description');
            $table->uuid('muscle_group_id');
            $table->uuid('trainer_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Foreign keys
            $table->foreign('muscle_group_id')
                  ->references('id')
                  ->on('muscle_groups')
                  ->onDelete('restrict');

            $table->foreign('trainer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Indexes
            $table->index('muscle_group_id');
            $table->index('trainer_id');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
}
