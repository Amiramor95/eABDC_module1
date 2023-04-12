<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEXTENSIONREQUESTAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('EXTENSION_REQUEST_APPROVAL', function (Blueprint $table) {
            $table->integer('EXTENSION_REQUEST_APPROVAL_ID', true);
            $table->integer('APPROVAL_GROUP_ID')->nullable();
            $table->integer('APPROVAL_LEVEL_ID')->nullable();
            $table->integer('EXTENSION_REQUEST_ID')->nullable();
            $table->mediumText('APPROVAL_REMARK')->nullable();
            $table->integer('TS_ID')->nullable();
            $table->integer('CREATED_BY')->nullable();
            $table->boolean('APPROVAL_PUBLISH_STATUS')->nullable()->default(false);
            $table->boolean('IS_FIMM')->default(false);
            $table->boolean('IS_SUBSEQUENT')->default(false);
            $table->date('APPROVAL_DATE')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('EXTENSION_REQUEST_APPROVAL');
    }
}
