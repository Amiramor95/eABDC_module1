<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERREGISTRATIONTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER_REGISTRATION', function (Blueprint $table) {
            $table->integer('USER_REGI_ID', true);
            $table->string('USER_REGI_NAME', 100)->nullable();
            $table->string('USER_ID', 45)->nullable();
            $table->integer('USER_REGI_DIST_ID')->nullable();
            $table->boolean('USER_REGI_STATUS')->nullable();
            $table->string('USER_REGI_ROLE', 50);
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
        Schema::dropIfExists('USER_REGISTRATION');
    }
}
