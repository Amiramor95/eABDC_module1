<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERDIVISIONTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER_DIVISION', function (Blueprint $table) {
            $table->integer('USER_DIVISION_ID')->primary();
            $table->string('USER_ID', 45)->nullable();
            $table->string('USER_DEPARTMENT', 45)->nullable();
            $table->string('USER_DIVISION', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('USER_DIVISION');
    }
}
