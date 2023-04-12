<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERREGISTRATIONDOCUMENTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER_REGISTRATION_DOCUMENT', function (Blueprint $table) {
            $table->integer('USER_REGI_DOCU_ID', true);
            $table->integer('USER_REGI_ID')->index('USER_REGI_ID');
            $table->integer('USER_ID')->nullable();
            $table->string('DOCU_FILETYPE', 100)->nullable();
            $table->string('DOCU_FILENAME', 100)->nullable();
            $table->string('DOCU_FILEPATH', 100)->nullable();
            $table->string('DOCU_DESCRIPTION', 500)->nullable();
            $table->integer('REQ_DOCU_ID')->nullable();
            $table->string('DOCU_MIMETYPE', 100)->nullable();
            $table->string('DOCU_FILETYPE_copy1', 40)->nullable();
            $table->string('DOCU_ORIGINAL_NAME', 100)->nullable();
            $table->binary('DOCU_BLOB')->nullable();
            $table->string('DOCU_FILESIZE', 100)->nullable();
            $table->integer('DOCU_GROUP')->nullable()->comment('1-proposal,    2-required,    3-image,    4-SSMForm9,    5-SSMForm8,    6-BODApprove,    7-receipt');
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
        Schema::dropIfExists('USER_REGISTRATION_DOCUMENT');
    }
}
