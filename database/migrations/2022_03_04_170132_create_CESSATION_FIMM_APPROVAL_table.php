<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCESSATIONFIMMAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CESSATION_FIMM_APPROVAL', function (Blueprint $table) {
            $table->integer('CESSATION_FIMM_APPROVAL_ID', true);
            $table->integer('APPR_GROUP_ID');
            $table->integer('APPROVAL_LEVEL_ID');
            $table->integer('CESSATION_ID')->index('SUSP_ID');
            $table->string('APPR_REMARK', 1500);
            $table->integer('TS_ID');
            $table->tinyInteger('APPR_PUBLISH_STATUS')->default(0)->comment('0: SAVE AS DRAFT 1: SUBMIT');
            $table->integer('CREATE_BY');
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
        Schema::dropIfExists('CESSATION_FIMM_APPROVAL');
    }
}
