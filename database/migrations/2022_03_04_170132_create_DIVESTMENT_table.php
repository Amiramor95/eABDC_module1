<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDIVESTMENTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DIVESTMENT', function (Blueprint $table) {
            $table->integer('DIVE_ID', true);
            $table->integer('DIST_ID')->index('DIST_ID');
            $table->integer('DIST_USER_ID')->index('DIST_USER_ID')->comment('CREATED_BY (REFER TABLE USER ID IN DISTRIBUTOR MANAGEMENT)');
            $table->integer('DIVE_TYPE');
            $table->date('CESSATION_DATE')->nullable();
            $table->date('LEGAL_DATE')->nullable();
            $table->integer('CEASE_STATUS')->nullable()->comment('1-yes 2-no');
            $table->integer('SECOND_LEVEL')->nullable()->comment('1-yes 2-no');
            $table->integer('TS_ID');
            $table->integer('FIMM_TS_ID');
            $table->integer('LATEST_UPDATE_BY');
            $table->timestamp('LATEST_UPDATE')->useCurrentOnUpdate()->useCurrent();
            $table->integer('FIMM_LATEST_UPDATE')->comment('REFER USER ID IN USER TABLE ADMIN MANAGEMENT');
            $table->timestamp('FIMM_LATEST_UPDATE_BY')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('CREATE_TIMESTAMP')->useCurrent();
            $table->integer('DIST_ID_MERGE')->nullable()->comment('DIST ID TO BE MERGE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DIVESTMENT');
    }
}
