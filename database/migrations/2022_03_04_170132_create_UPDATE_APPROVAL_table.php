<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUPDATEAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('UPDATE_APPROVAL', function (Blueprint $table) {
            $table->integer('UPDATE_APPR_ID', true);
            $table->integer('DIST_TEMP_ID')->index('DIST_TEMP_ID');
            $table->integer('USER_TYPE');
            $table->integer('USER_ID')->index('USER_ID');
            $table->integer('USER_GROUP');
            $table->string('APPR_REMARK', 500);
            $table->integer('APPR_STATUS');
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
        Schema::dropIfExists('UPDATE_APPROVAL');
    }
}
