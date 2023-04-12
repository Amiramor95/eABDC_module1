<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUPDATEAPPROVALSECTIONDOCUMENTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('UPDATE_APPROVAL_SECTION_DOCUMENT', function (Blueprint $table) {
            $table->integer('UPDATE_APPR_SECT_DOCU_ID', true);
            $table->integer('UPDATE_APPR_SECT_REMARK_ID')->index('UPDATE_APPR_SECT_REMARK_ID');
            $table->string('DOCU_FILETYPE', 100);
            $table->string('DOCU_FILENAME', 100);
            $table->string('DOCU_FILEPATH', 100);
            $table->string('DOCU_DESCRIPTION', 100);
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
        Schema::dropIfExists('UPDATE_APPROVAL_SECTION_DOCUMENT');
    }
}
