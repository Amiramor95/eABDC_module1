<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUPDATEAPPROVALDOCUMENTREMARKTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('UPDATE_APPROVAL_DOCUMENT_REMARK', function (Blueprint $table) {
            $table->integer('UPDATE_APPR_DOCU_REMARK_ID', true);
            $table->integer('UPDATE_APPR_ID')->index('UPDATE_APPR_ID');
            $table->integer('DIST_TEMP_DOCU_ID')->index('DIST_TEMP_DOCU_ID');
            $table->string('DOCU_REMARK', 500);
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
        Schema::dropIfExists('UPDATE_APPROVAL_DOCUMENT_REMARK');
    }
}
