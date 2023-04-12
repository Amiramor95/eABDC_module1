<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPADDRESSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP_ADDRESS', function (Blueprint $table) {
            $table->integer('DIST_TEMP_ADDR_ID', true);
            $table->integer('DIST_TEMP_ID')->index('DIST_TEMP_ID');
            $table->string('DIST_ADDR_1', 100)->nullable()->default('-');
            $table->string('DIST_ADDR_2', 100)->nullable()->default('-');
            $table->string('DIST_ADDR_3', 100)->nullable()->default('-');
            $table->integer('DIST_POSTAL')->nullable();
            $table->integer('DIST_CITY')->nullable();
            $table->integer('DIST_STATE')->nullable();
            $table->integer('DIST_COUNTRY')->nullable();
            $table->string('DIST_POSTAL2', 100)->nullable();
            $table->string('DIST_STATE2', 100)->nullable();
            $table->string('DIST_CITY2', 100)->nullable();
            $table->string('DIST_ADDR_SAME', 2)->nullable()->default('-');
            $table->string('DIST_BIZ_ADDR_1', 100)->nullable()->default('-');
            $table->string('DIST_BIZ_ADDR_2', 100)->nullable()->default('-');
            $table->string('DIST_BIZ_ADDR_3', 100)->nullable()->default('-');
            $table->integer('DIST_BIZ_POSTAL')->nullable();
            $table->integer('DIST_BIZ_CITY')->nullable();
            $table->integer('DIST_BIZ_STATE')->nullable();
            $table->integer('DIST_BIZ_COUNTRY')->nullable();
            $table->string('DIST_BIZ_POSTAL2', 100)->nullable();
            $table->string('DIST_BIZ_STATE2', 100)->nullable();
            $table->string('DIST_BIZ_CITY2', 100)->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_TEMP_ADDRESS');
    }
}
