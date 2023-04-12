<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSUSPENDREVOKETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SUSPEND_REVOKE', function (Blueprint $table) {
            $table->integer('SUSPEND_REVOKE_ID', true);
            $table->integer('DISTRIBUTOR_ID')->nullable()->index('DIST_ID');
            $table->integer('SUBMISSION_TYPE')->nullable()->comment('1:SUSPEND, 2 :REVOKE');
            $table->date('DATE_START')->nullable();
            $table->date('DATE_END')->nullable();
            $table->date('EFFECTIVE_DATE')->nullable();
            $table->string('REASON', 1500)->nullable();
            $table->tinyInteger('PUBLISH_STATUS')->nullable()->default(0)->comment('0:DRAFT,1:SUBMIT');
            $table->integer('CREATE_BY')->nullable();
            $table->integer('TS_ID')->nullable();
            $table->integer('LATEST_UPDATE_BY')->nullable();
            $table->timestamp('LATEST_UPDATE')->useCurrentOnUpdate()->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
            $table->tinyInteger('ISSUBMIT')->default(0)->comment('0: NO 1:YES');
            $table->tinyInteger('DIST_ACTION')->default(0)->comment('1: ACCEPT, 2:APPEAL');
            $table->date('APPEAL_END')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('SUSPEND_REVOKE');
    }
}
