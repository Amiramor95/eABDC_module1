<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORFINANCIALPLANNERTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_FINANCIAL_PLANNER', function (Blueprint $table) {
            $table->integer('DIST_FP_ID', true);
            $table->integer('DIST_ID');
            $table->integer('DIST_TYPE_ID');
            $table->integer('DIST_FINANCIAL_INSTITUTION')->nullable();
            $table->integer('DIST_FP_SALUTATION')->nullable();
            $table->string('DIST_FP_NAME', 100)->nullable();
            $table->string('DIST_FP_CSMRL', 45)->nullable()->default('-');
            $table->integer('DIST_FP_CITIZEN')->nullable();
            $table->string('DIST_FP_NRIC', 45)->nullable()->default('-');
            $table->string('DIST_FP_PASS_NUM', 45)->nullable()->default('-');
            $table->date('DIST_FP_PASS_EXPIRY')->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_FINANCIAL_PLANNER');
    }
}
