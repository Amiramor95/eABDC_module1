<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateACCEPTANCEDETAILSREJECTEDTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ACCEPTANCE_DETAILS_REJECTED', function (Blueprint $table) {
            $table->integer('ACCEPTANCE_DETAILS_REJECTED_ID', true);
            $table->integer('CANDIDATE_ACCEPTANCE_ID')->nullable();
            $table->string('CANDIDATE_NAME', 250)->nullable();
            $table->string('CANDIDATE_NRIC', 12)->nullable();
            $table->string('CANDIDATE_PASSPORT_NO', 50)->nullable();
            $table->string('CANDIDATE_EMAIL', 100)->nullable();
            $table->string('CANDIDATE_PHONENO', 11)->nullable();
            $table->integer('LICENSE_TYPE')->nullable()->comment('REFER SETTING CONSULTANT TYPE');
            $table->integer('STAFF_OR_AGENT')->nullable()->comment('1: STAFF 2:AGENT');
            $table->integer('CA_CLASSIFICATION')->nullable()->comment('JOIN WITH CONSULTANT ALERT');
            $table->string('REASON', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ACCEPTANCE_DETAILS_REJECTED');
    }
}
