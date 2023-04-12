<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPDIRECTORTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP_DIRECTOR', function (Blueprint $table) {
            $table->integer('DIST_TEMP_DIR_ID', true);
            $table->integer('DIST_TEMP_ID')->index('DIST_TEMP_ID');
            $table->integer('DIR_SALUTATION')->nullable();
            $table->string('DIR_NAME', 100)->nullable();
            $table->string('DIR_NRIC', 14)->nullable()->default('-');
            $table->string('DIR_PASS_NUM', 45)->nullable()->default('-');
            $table->date('DIR_PASS_EXPIRY')->nullable();
            $table->date('DIR_DATE_EFFECTIVE')->nullable();
            $table->date('DIR_DATE_END')->nullable();
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
        Schema::dropIfExists('DISTRIBUTOR_TEMP_DIRECTOR');
    }
}
