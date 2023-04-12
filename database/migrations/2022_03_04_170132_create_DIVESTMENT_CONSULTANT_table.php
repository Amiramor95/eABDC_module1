<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDIVESTMENTCONSULTANTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DIVESTMENT_CONSULTANT', function (Blueprint $table) {
            $table->integer('DIVE_CONS_ID', true);
            $table->integer('DIVE_ID')->index('DIVE_ID');
            $table->integer('CONS_ID');
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
        Schema::dropIfExists('DIVESTMENT_CONSULTANT');
    }
}
