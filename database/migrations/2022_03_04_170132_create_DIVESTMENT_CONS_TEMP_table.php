<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDIVESTMENTCONSTEMPTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DIVESTMENT_CONS_TEMP', function (Blueprint $table) {
            $table->integer('DIVE_CONS_TEMP_ID', true);
            $table->integer('DIST_ID');
            $table->integer('CONS_ID');
            $table->integer('DIVE_SELECTED');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DIVESTMENT_CONS_TEMP');
    }
}
