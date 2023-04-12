<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_APPROVAL', function (Blueprint $table) {
            $table->integer('DIST_APPROVAL_ID', true);
            $table->integer('DIST_ID');
            $table->integer('DIST_TYPE_ID')->nullable();
            $table->integer('APPR_GROUP_ID')->nullable();
            $table->integer('APPROVAL_LEVEL_ID')->nullable();
            $table->integer('APPROVAL_INDEX')->nullable();
            $table->tinyInteger('APPROVAL_STATUS')->nullable();
            $table->integer('APPROVAL_FIMM_USER')->nullable();
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
        Schema::dropIfExists('DISTRIBUTOR_APPROVAL');
    }
}
