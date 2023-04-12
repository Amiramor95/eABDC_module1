<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPDOCUMENTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP_DOCUMENT', function (Blueprint $table) {
            $table->integer('DIST_TEMP_DOCU_ID', true);
            $table->integer('DIST_TEMP_ID')->index('DIST_TEMP_ID');
            $table->string('DOCU_MIMETYPE', 100)->nullable();
            $table->string('DOCU_FILETYPE', 40)->nullable();
            $table->string('DOCU_ORIGINAL_NAME', 100)->nullable();
            //$table->binary('DOCU_BLOB')->nullable();
            $table->string('DOCU_FILESIZE', 100)->nullable();
            $table->integer('DOCU_GROUP')->nullable()->comment('1-proposal, 2-required, 3-image, 4-SSMForm9, 5-SSMForm8, 6-BODApprove, 7-receipt');
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
        });

        DB::statement("ALTER TABLE DISTRIBUTOR_TEMP_DOCUMENT ADD DOCU_BLOB MEDIUMBLOB");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_TEMP_DOCUMENT');
    }
}
