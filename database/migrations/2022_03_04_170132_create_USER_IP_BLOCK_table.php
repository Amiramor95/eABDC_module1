<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERIPBLOCKTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER_IP_BLOCK', function (Blueprint $table) {
            $table->integer('BLOCK_ID', true);
            $table->string('USER_NAME')->nullable();
            $table->string('USER_IP')->nullable();
            $table->integer('BLOCK_STATUS')->default(0)->comment('0: UNBLOCK, 1: BLOCK');
            $table->timestamp('BLOCK_TIME')->nullable();
            $table->timestamp('UNBLOCK_TIME')->nullable();
            $table->integer('BLOCK_DURATION')->comment('minutes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('USER_IP_BLOCK');
    }
}
