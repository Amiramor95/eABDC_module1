<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPDOCUMENTREMARKTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP_DOCUMENT_REMARK', function (Blueprint $table) {
            $table->integer('DIST_TEMP_DOCUMENT_REMARK_ID', true);
            $table->integer('DIST_TEMP_ID')->nullable();
            $table->integer('DIST_UPDATE_APPROVAL_ID')->nullable();
            $table->binary('DOCU_BLOB')->nullable();
            $table->string('DOCU_FILETYPE', 40)->nullable();
            $table->integer('DOCU_FILESIZE')->nullable();
            $table->string('DOCU_ORIGINAL_NAME', 100)->nullable();
            $table->integer('DOCU_TYPE')->nullable()->comment('1 - profile, 2 Details Info, 3 CEO Info, 4 AR / AAR Info, 5 Additional Info, 6 Payment Info');
            $table->integer('CREATE_BY')->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_TEMP_DOCUMENT_REMARK');
    }
}
