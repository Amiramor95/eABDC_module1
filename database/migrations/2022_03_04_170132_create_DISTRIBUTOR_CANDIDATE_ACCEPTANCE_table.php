<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORCANDIDATEACCEPTANCETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_CANDIDATE_ACCEPTANCE', function (Blueprint $table) {
            $table->integer('DISTRIBUTOR_CANDIDATE_ACCEPTANCE_ID', true);
            $table->integer('DIST_ID')->index('DISTRIBUTOR_ID');
            $table->string('BATCH_NO', 100);
            $table->string('CANDIDATE_NAME', 300);
            $table->string('CANDIDATE_NRIC', 15)->nullable();
            $table->string('CANDIDATE_EMAIL', 100);
            $table->string('CANDIDATE_PHONENO', 15)->nullable();
            $table->string('CANDIDATE_LICENSE_TYPE', 50);
            $table->string('CANDIDATE_STAFF_AGENT', 20);
            $table->string('CANDIDATE_CONSULTANT_ALERT', 50);
            $table->string('CANDIDATE_PASSPORTNO', 20)->nullable();
            $table->timestamp('CREATED_DATE')->useCurrent();
            $table->integer('TS_ID')->nullable();
            $table->integer('PUBLISH_STATUS')->default(0)->comment('1: SUBMIT 0:SAVE AS DRAFT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_CANDIDATE_ACCEPTANCE');
    }
}
