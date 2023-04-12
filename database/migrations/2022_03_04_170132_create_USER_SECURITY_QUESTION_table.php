<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERSECURITYQUESTIONTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER_SECURITY_QUESTION', function (Blueprint $table) {
            $table->integer('SECURITY_ID', true);
            $table->string('SECURITY_QUESTION', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('USER_SECURITY_QUESTION');
    }
}
