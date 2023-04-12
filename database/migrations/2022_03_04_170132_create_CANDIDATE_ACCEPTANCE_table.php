<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCANDIDATEACCEPTANCETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CANDIDATE_ACCEPTANCE', function (Blueprint $table) {
            $table->integer('CANDIDATE_ACCEPTANCE_ID', true);
            $table->integer('DISTRIBUTOR_ID')->nullable();
            $table->string('REFERENCE_NO', 500)->nullable();
            $table->integer('CREATE_BY')->nullable()->comment('GET FROM USER_ID (ADMIN TBL)');
            $table->timestamp('CREATE_TIMESTAMP')->useCurrent();
            $table->integer('TS_ID')->nullable()->comment('GET FROM TASK_STATUS(ADMIN TBL)');
            $table->integer('PUBLISH_STATUS')->default(0)->comment('0: SAVE AS DRAFT 1: SUBMIT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CANDIDATE_ACCEPTANCE');
    }
}
