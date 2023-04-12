<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRUNNOTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DIST_RUNNO', function (Blueprint $table) {
            $table->integer('DIST_RUNNO_ID', true);
            $table->integer('DISTRIBUTOR_ID')->nullable();
            $table->string('DISTRIBUTOR_CODE', 350)->nullable();
            $table->integer('START_NO')->nullable()->default(10000);
            $table->integer('CURRENT_NO')->nullable()->default(10000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DIST_RUNNO');
    }
}
