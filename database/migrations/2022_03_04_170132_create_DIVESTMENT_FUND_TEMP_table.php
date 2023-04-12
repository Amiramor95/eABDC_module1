<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDIVESTMENTFUNDTEMPTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DIVESTMENT_FUND_TEMP', function (Blueprint $table) {
            $table->integer('DIVE_FUND_TEMP_ID', true);
            $table->integer('DIST_ID');
            $table->integer('FUND_PROFILE_ID');
            $table->integer('DIVE_SELECTED')->comment('1-yes 2-no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DIVESTMENT_FUND_TEMP');
    }
}
