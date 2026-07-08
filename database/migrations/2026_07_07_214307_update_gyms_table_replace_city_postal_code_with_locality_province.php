<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateGymsTableReplaceCityPostalCodeWithLocalityProvince extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->dropColumn(['city', 'postal_code']);
            $table->string('locality', 100)->after('address');
            $table->string('province', 100)->after('locality');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->dropColumn(['locality', 'province']);
            $table->string('city', 100)->after('address');
            $table->string('postal_code', 20)->after('city');
        });
    }
}
