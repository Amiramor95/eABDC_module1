<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEXTENSIONREQUESTDOCUMENTSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('EXTENSION_REQUEST_DOCUMENTS', function (Blueprint $table) {
            $table->integer('EXTENSION_REQUEST_DOCUMENT_ID', true);
            $table->integer('EXTENSION_REQUEST_ID');
            $table->string('DOCUMENT_NAME');
            $table->binary('DOCUMENT_BLOB')->nullable();
            $table->string('DOCUMENT_TYPE');
            $table->integer('DOCUMENT_SIZE')->default(0);
            $table->boolean('IS_ACTION_PLAN')->default(false)->comment('0 STANDARD DOCUMENT , 1 FOR ACTION PLAN DOCUMENT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('EXTENSION_REQUEST_DOCUMENTS');
    }
}
