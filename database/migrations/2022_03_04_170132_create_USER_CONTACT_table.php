<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERCONTACTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER_CONTACT', function (Blueprint $table) {
            $table->integer('USER_CONT_ID');
            $table->integer('USER_ID')->index('USER_ID');
            $table->string('USER_TELEPHONE_1', 50);
            $table->string('USER_TELEPHONE_2', 50);
            $table->string('USER_FAX_1', 50);
            $table->string('USER_FAX_2', 50);
            $table->string('USER_EMAIL_1', 100);
            $table->string('USER_EMAIL_2', 100);
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
        Schema::dropIfExists('USER_CONTACT');
    }
}
