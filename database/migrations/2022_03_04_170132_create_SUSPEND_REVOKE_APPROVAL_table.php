<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSUSPENDREVOKEAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SUSPEND_REVOKE_APPROVAL', function (Blueprint $table) {
            $table->integer('SUSPEND_REVOKE_APPROVAL_ID', true);
            $table->integer('APPR_GROUP_ID');
            $table->integer('APPROVAL_LEVEL_ID');
            $table->integer('SUSPEND_REVOKE_ID')->index('SUSP_ID');
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
        Schema::dropIfExists('SUSPEND_REVOKE_APPROVAL');
    }
}
