<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPADDITIONALINFOTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP_ADDITIONAL_INFO', function (Blueprint $table) {
            $table->integer('DIST_TEMP_ADDITIONAL_INFO_ID', true);
            $table->integer('DIST_TEMP_ID');
            $table->string('ADD_TYPE', 10)->nullable();
            $table->integer('ADD_SALUTATION')->nullable();
            $table->string('ADD_NAME', 100)->nullable()->default('-');
            $table->string('ADD_TELEPHONE', 12)->nullable()->default('-');
            $table->string('ADD_PHONE_EXTENSION', 10)->nullable()->default('-');
            $table->string('ADD_EMAIL', 100)->nullable()->default('-');
            $table->string('ADD_MOBILE_NUMBER', 12)->nullable()->default('-');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_TEMP_ADDITIONAL_INFO');
    }
}
