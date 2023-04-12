<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPLEDGERTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP_LEDGER', function (Blueprint $table) {
            $table->integer('DIST_TEMP_LEDG_ID', true);
            $table->integer('DIST_TEMP_ID')->index('DIST_TEMP_ID');
            $table->integer('DIST_TYPE_ID');
            $table->string('DIST_TRANS_REF', 100)->nullable();
            $table->date('DIST_TRANS_DATE')->nullable();
            $table->tinyInteger('DIST_TRANS_TYPE')->nullable();
            $table->string('DIST_ACC_AMOUNT', 100)->nullable()->default('-');
            $table->string('DIST_ISSUEBANK', 45)->nullable()->default('-');
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
        Schema::dropIfExists('DISTRIBUTOR_TEMP_LEDGER');
    }
}
