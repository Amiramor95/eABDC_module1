<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPCONTACTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP_CONTACT', function (Blueprint $table) {
            $table->integer('DIST_TEMP_CONT_ID', true);
            $table->integer('DIST_TEMP_ID')->index('DIST_TEMP_ID');
            $table->string('DIST_TELEPHONE_1', 50)->default('-');
            $table->string('DIST_TELEPHONE_2', 50)->default('-');
            $table->string('DIST_FAX_1', 50)->default('-');
            $table->string('DIST_FAX_2', 50)->default('-');
            $table->string('DIST_EMAIL_1', 100)->default('-');
            $table->string('DIST_EMAIL_2', 100)->default('-');
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
        Schema::dropIfExists('DISTRIBUTOR_TEMP_CONTACT');
    }
}
