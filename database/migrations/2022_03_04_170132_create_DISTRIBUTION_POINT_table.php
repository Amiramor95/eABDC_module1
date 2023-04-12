<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTIONPOINTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTION_POINT', function (Blueprint $table) {
            $table->integer('DIST_POINT_ID', true);
            $table->integer('DISTRIBUTOR_ID')->nullable();
            $table->string('DIST_POINT_CODE', 100)->nullable();
            $table->string('DIST_POINT_NAME', 500)->nullable();
            $table->string('PHONE_NUMBER', 20)->nullable();
            $table->string('DIST_ADDR_1', 500)->nullable();
            $table->string('DIST_ADDR_2', 300)->nullable();
            $table->string('DIST_ADDR_3', 300)->nullable();
            $table->integer('DIST_POSTAL')->nullable();
            $table->integer('DIST_CITY')->nullable();
            $table->integer('DIST_COUNTRY')->nullable();
            $table->integer('DIST_STATE')->nullable();
            $table->string('OTHER_STATE', 500)->nullable();
            $table->string('OTHER_CITY', 500)->nullable();
            $table->string('OTHER_POSTAL', 100)->nullable();
            $table->integer('TS_ID')->nullable();
            $table->integer('CREATE_BY')->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
            $table->timestamp('LATEST_UPDATE')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTION_POINT');
    }
}
