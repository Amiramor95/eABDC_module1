<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDIVESTMENTDISTAPPRDOCTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DIVESTMENT_DISTAPPR_DOC', function (Blueprint $table) {
            $table->integer('DIVE_DISTAPPR_DOCU_ID', true);
            $table->integer('DIVE_ID')->index('DIVE_ID');
            $table->string('DOC_MIMETYPE', 100)->nullable();
            $table->string('DOC_FILETYPE', 40)->nullable();
            $table->string('DOC_ORIGINAL_NAME', 200)->nullable();
            $table->binary('DOC_BLOB')->nullable();
            $table->decimal('DOC_FILESIZE', 10)->nullable();
            $table->integer('CREATE_BY')->nullable();
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
        Schema::dropIfExists('DIVESTMENT_DISTAPPR_DOC');
    }
}
