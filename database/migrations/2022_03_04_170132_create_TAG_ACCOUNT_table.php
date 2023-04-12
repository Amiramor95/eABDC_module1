<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTAGACCOUNTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TAG_ACCOUNT', function (Blueprint $table) {
            $table->integer('TAG_ACCOUNT_ID', true);
            $table->integer('TAG_DISTRIBUTOR_ID');
            $table->integer('TAG_CONSULTANT_ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TAG_ACCOUNT');
    }
}
