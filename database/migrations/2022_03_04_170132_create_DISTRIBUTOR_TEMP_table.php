<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP', function (Blueprint $table) {
            $table->integer('DIST_TEMP_ID', true);
            $table->integer('DIST_ID')->index('DIST_ID');
            $table->string('DIST_NAME', 100)->nullable();
            $table->integer('DIST_REGI_NUM1')->nullable();
            $table->integer('DIST_REGI_NUM2')->nullable();
            $table->integer('DIST_TYPE_SETUP')->nullable();
            $table->string('DIST_PHONE_NUMBER', 100)->nullable();
            $table->string('DIST_PHONE_EXTENSION', 50)->nullable();
            $table->string('DIST_MOBILE_NUMBER', 15)->nullable();
            $table->string('DIST_FAX_NUMBER', 100)->nullable();
            $table->string('DIST_EMAIL', 100)->nullable();
            $table->string('DIST_COMPANY_WEBSITE', 100)->nullable();
            $table->integer('DIST_TEMP_CATEGORY')->default(0)->comment('1: UPFATE PROFILE 2:REGISTER NEW LICENSE');
            $table->integer('TS_ID')->nullable()->comment('REFER TASK_STATUS AT ADMIN_MANAGEMENT ');
            $table->boolean('PUBLISH_STATUS')->default(false)->comment('1: SUBMIT 0:SAVE AS DRAFT');
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
        Schema::dropIfExists('DISTRIBUTOR_TEMP');
    }
}
