<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCESSATIONDISTRIBUTORAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CESSATION_DISTRIBUTOR_APPROVAL', function (Blueprint $table) {
            $table->integer('CESSATION_DISTRIBUTOR_APPROVAL_ID', true);
            $table->integer('APPR_GROUP_ID')->nullable();
            $table->integer('APPROVAL_LEVEL_ID')->nullable()->comment('REFER TO DISTRIBUTOR_APPROVAL_LEVEL_ID');
            $table->integer('CESSATION_ID')->nullable()->index('SUSP_ID');
            $table->string('APPR_REMARK', 1500)->nullable();
            $table->integer('TS_ID')->nullable();
            $table->tinyInteger('APPR_PUBLISH_STATUS')->default(0)->comment('0: SAVE AS DRAFT 1: SUBMIT');
            $table->integer('CREATE_BY')->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->useCurrent();
            $table->timestamp('LATEST_UPDATE')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CESSATION_DISTRIBUTOR_APPROVAL');
    }
}
