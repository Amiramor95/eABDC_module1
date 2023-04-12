<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORADDRESSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_ADDRESS', function (Blueprint $table) {
            $table->integer('DIST_ADDR_ID', true);
            $table->integer('DIST_ID')->nullable()->unique('DIST_ID');
            $table->string('DIST_ADDR_1', 100)->nullable();
            $table->string('DIST_ADDR_2', 100)->nullable();
            $table->string('DIST_ADDR_3', 100)->nullable()->default('-');
            $table->integer('DIST_POSTAL')->nullable();
            $table->integer('DIST_CITY')->nullable();
            $table->integer('DIST_STATE')->nullable();
            $table->integer('DIST_COUNTRY')->nullable();
            $table->string('DIST_POSTAL2', 100)->nullable();
            $table->string('DIST_STATE2', 100)->nullable();
            $table->string('DIST_CITY2', 100)->nullable();
            $table->string('DIST_ADDR_SAME', 2)->nullable()->default('-');
            $table->string('DIST_BIZ_ADDR_1', 100)->nullable();
            $table->string('DIST_BIZ_ADDR_2', 100)->nullable()->default('-');
            $table->string('DIST_BIZ_ADDR_3', 100)->nullable()->default('-');
            $table->integer('DIST_BIZ_POSTAL')->nullable();
            $table->integer('DIST_BIZ_CITY')->nullable();
            $table->integer('DIST_BIZ_STATE')->nullable();
            $table->integer('DIST_BIZ_COUNTRY')->nullable();
            $table->string('DIST_BIZ_POSTAL2', 100)->nullable();
            $table->string('DIST_BIZ_STATE2', 100)->nullable();
            $table->string('DIST_BIZ_CITY2', 100)->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_ADDRESS');
    }
}
