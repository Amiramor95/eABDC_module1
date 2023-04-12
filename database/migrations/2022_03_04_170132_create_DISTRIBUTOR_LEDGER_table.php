<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORLEDGERTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_LEDGER', function (Blueprint $table) {
            $table->integer('DIST_LEDG_ID', true);
            $table->integer('DIST_ID');
            $table->integer('DIST_TYPE_ID')->nullable();
            $table->string('DIST_TRANS_REF', 100)->nullable();
            $table->date('DIST_TRANS_DATE')->nullable();
            $table->tinyInteger('DIST_TRANS_TYPE')->nullable()->comment('1 - Online Transaction   2 - Cash Deposite');
            $table->string('DIST_ACC_AMOUNT', 100)->nullable();
            $table->string('DIST_ISSUEBANK', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_LEDGER');
    }
}
