<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSUSPENDREVOKEAPPEALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SUSPEND_REVOKE_APPEAL', function (Blueprint $table) {
            $table->integer('SUSPEND_REVOKE_APPEAL_ID', true);
            $table->integer('SUSPEND_REVOKE_ID');
            $table->string('JUSTIFICATION', 1500)->nullable();
            $table->tinyInteger('PUBLISH_STATUS')->nullable()->default(0)->comment('0:DRAFT,1:SUBMIT');
            $table->integer('CREATE_BY')->nullable();
            $table->integer('TS_ID')->nullable();
            $table->integer('FIMM_TS_ID')->nullable();
            $table->integer('LATEST_UPDATE_BY')->nullable();
            $table->timestamp('LATEST_UPDATE')->useCurrentOnUpdate()->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
            $table->tinyInteger('ISSUBMIT')->default(0)->comment('0: NO 1:YES');
            $table->tinyInteger('FIMM_ISSUBMIT')->default(0)->comment('0: NO 1:YES');
            $table->string('FIMM_REMARK', 1500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('SUSPEND_REVOKE_APPEAL');
    }
}
