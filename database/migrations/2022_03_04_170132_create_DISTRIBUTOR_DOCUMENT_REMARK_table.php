<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORDOCUMENTREMARKTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_DOCUMENT_REMARK', function (Blueprint $table) {
            $table->integer('DIST_DOCU_REMARK_ID', true);
            $table->integer('DIST_ID');
            $table->integer('DIST_TYPE_ID');
            $table->integer('DIST_APPR_ID')->nullable()->index('DIST_APPR_ID_idx');
            $table->binary('DOCU_BLOB')->nullable();
            $table->string('DOCU_FILETYPE', 40)->nullable();
            $table->integer('DOCU_FILESIZE')->nullable();
            $table->string('DOCU_ORIGINAL_NAME', 100)->nullable();
            $table->integer('DOCU_TYPE')->nullable()->comment('1 - profile');
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
        Schema::dropIfExists('DISTRIBUTOR_DOCUMENT_REMARK');
    }
}
