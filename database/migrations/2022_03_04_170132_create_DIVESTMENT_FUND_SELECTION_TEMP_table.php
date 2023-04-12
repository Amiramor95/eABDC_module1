<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDIVESTMENTFUNDSELECTIONTEMPTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DIVESTMENT_FUND_SELECTION_TEMP', function (Blueprint $table) {
            $table->integer('DIVE_FUND_SELECTION_ID', true);
            $table->integer('DIST_ID');
            $table->integer('FUND_PROFILE_ID');
            $table->integer('FUND_CODE_FIMM');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DIVESTMENT_FUND_SELECTION_TEMP');
    }
}
