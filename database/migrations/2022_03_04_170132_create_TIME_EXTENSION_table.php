<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTIMEEXTENSIONTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TIME_EXTENSION', function (Blueprint $table) {
            $table->integer('TIME_EXTENSION_ID', true);
            $table->integer('DISTRIBUTOR_ADMIN_ID');
            $table->integer('TIME_EXTENSION');
            $table->timestamp('CREATED_DATE')->useCurrent();
            $table->timestamp('UPDATED_DATE')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TIME_EXTENSION');
    }
}
