<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERREGISTRATIONAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER_REGISTRATION_APPROVAL', function (Blueprint $table) {
            $table->integer('USER_REGI_APPR_ID', true);
            $table->integer('USER_REGI_ID')->nullable()->index('USER_REGI_ID');
            $table->integer('USER_ID')->nullable()->index('USER_ID');
            $table->integer('USER_DIST_ID')->nullable();
            $table->integer('APPR_GROUP_ID')->nullable();
            $table->integer('APPROVAL_LEVEL_ID')->nullable();
            $table->string('APPR_REMARK', 500)->nullable();
            $table->string('APPR_STATUS', 500)->nullable();
            $table->integer('APPR_PUBLISH_STATUS')->nullable();
            $table->timestamp('LATEST_UPDATE')->useCurrentOnUpdate()->nullable();
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
        Schema::dropIfExists('USER_REGISTRATION_APPROVAL');
    }
}
