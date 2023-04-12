<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORUPDATEAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_UPDATE_APPROVAL', function (Blueprint $table) {
            $table->integer('DISTRIBUTOR_UPDATE_APPROVAL_ID', true);
            $table->integer('DIST_TEMP_ID');
            $table->integer('DIST_ID');
            $table->integer('APPR_GROUP_ID')->nullable()->comment('1-2nd level dist
4- rd

Based on group id');
            $table->integer('APPROVAL_LEVEL_ID')->nullable();
            $table->integer('APPROVAL_INDEX')->nullable();
            $table->integer('APPROVAL_STATUS')->nullable();
            $table->integer('APPROVAL_USER')->nullable();
            $table->string('APPROVAL_REMARK_PROFILE', 300)->nullable();
            $table->string('APPROVAL_REMARK_DETAILINFO', 300)->nullable();
            $table->string('APPROVAL_REMARK_CEOnDIR', 300)->nullable();
            $table->string('APPROVAL_REMARK_ARnAAR', 300)->nullable();
            $table->string('APPROVAL_REMARK_ADDTIONALINFO', 300)->nullable();
            $table->string('APPROVAL_REMARK_PAYMENT', 300)->nullable();
            $table->timestamp('APPROVAL_DATE')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_UPDATE_APPROVAL');
    }
}
