<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERPASSPORTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER_PASSPORT', function (Blueprint $table) {
            $table->integer('USER_PASS_ID', true);
            $table->integer('USER_ID')->index('USER_ID');
            $table->string('USER_PASS_NUM', 50);
            $table->date('USER_PASS_EXP');
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
        Schema::dropIfExists('USER_PASSPORT');
    }
}
