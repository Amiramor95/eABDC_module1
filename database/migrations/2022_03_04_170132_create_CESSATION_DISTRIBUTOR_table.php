<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCESSATIONDISTRIBUTORTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CESSATION_DISTRIBUTOR', function (Blueprint $table) {
            $table->integer('CESSATION_ID', true);
            $table->integer('DISTRIBUTOR_ID')->nullable()->index('DIST_ID');
            $table->integer('CESSATION_TYPE')->nullable()->comment('REFER SETTING_GENERAL, SET_TYPE : CESATIONTYPE');
            $table->date('CESSATION_DATE')->nullable();
            $table->date('LEGAL_DATE')->nullable();
            $table->integer('DISTRIBUTOR_MERGER')->nullable()->comment('WILL REFER DISTRIBUTOR ID');
            $table->string('OTHER_REMARK', 1500)->nullable();
            $table->string('RECIPIENT_NAME', 500)->nullable();
            $table->integer('BANK_ID')->nullable()->comment('REFER SETTING_GENERAL SET_TYPE: BANK');
            $table->string('ACCOUNT_NO', 20)->nullable();
            $table->tinyInteger('PUBLISH_STATUS')->nullable()->default(0)->comment('0:DRAFT,1:SUBMIT');
            $table->integer('CREATE_BY')->nullable();
            $table->integer('TS_ID')->nullable();
            $table->integer('FIMM_TS_ID')->nullable();
            $table->integer('LATEST_UPDATE_BY')->nullable();
            $table->timestamp('LATEST_UPDATE')->useCurrentOnUpdate()->nullable();
            $table->timestamp('FIMM_LATEST_UPDATE')->useCurrentOnUpdate()->nullable();
            $table->integer('FIMM_LATEST_UPDATE_BY')->nullable();
            $table->string('FIMM_REMARK', 1500)->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
            $table->date('SUBMISSION_DATE')->nullable();
            $table->tinyInteger('ISSUBMIT')->default(0)->comment('0: NO 1:YES');
            $table->date('CEASE_NOTIFICATION')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CESSATION_DISTRIBUTOR');
    }
}
