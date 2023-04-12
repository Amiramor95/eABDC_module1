<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCESSATIONFIMMDOCTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CESSATION_FIMM_DOC', function (Blueprint $table) {
            $table->integer('CFD_DOCUMENT_ID', true);
            $table->integer('CESSATION_ID')->nullable();
            $table->integer('CESSATION_FIMM_APPROVAL_ID')->nullable()->index('CA_RECORD_DETAILS_ID');
            $table->string('DOC_MIMETYPE', 100)->nullable();
            $table->string('DOC_FILETYPE', 40)->nullable();
            $table->string('DOC_ORIGINAL_NAME', 200)->nullable();
            $table->binary('DOC_BLOB')->nullable();
            $table->decimal('DOC_FILESIZE', 10)->nullable();
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
        Schema::dropIfExists('CESSATION_FIMM_DOC');
    }
}
