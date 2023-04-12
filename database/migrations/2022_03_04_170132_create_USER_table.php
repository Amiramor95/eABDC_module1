<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUSERTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('USER', function (Blueprint $table) {
            $table->integer('USER_ID', true);
            $table->string('KEYCLOAK_ID', 100)->nullable();
            $table->string('USER_NAME', 100)->nullable();
            $table->integer('USER_CITIZEN');
            $table->string('USER_NRIC', 12)->nullable();
            $table->date('USER_DOB')->nullable();
            $table->integer('USER_GROUP');
            $table->string('USER_EMAIL', 100);
            $table->string('USER_MOBILE_NUM', 50)->nullable();
            $table->string('USER_OFFICE_NUM', 50)->nullable();
            $table->string('USER_EXTENSION_NUM', 50)->nullable();
            $table->string('USER_PASS_NUM', 100)->nullable();
            $table->dateTime('USER_PASS_EXP')->nullable();
            $table->string('USER_USER_ID', 100)->nullable();
            $table->string('USER_PASSWORD', 100)->nullable();
            $table->integer('USER_SECURITY_QUESTION_ID')->nullable();
            $table->string('USER_SECURITY_ANSWER', 100)->nullable();
            $table->string('USER_STATUS', 100)->nullable()->default('0')->comment('01:ACTIVE, 2: INACTIVE');
            $table->tinyInteger('USER_ISLOGIN')->nullable()->default(0)->comment('0 - first\\r\\n1 - not first');
            $table->integer('USER_ISADMIN')->nullable();
            $table->integer('USER_DIST_ID')->nullable()->default(0)->comment('dm - DISTRIBUTOR');
            $table->timestamp('LOGINTIME')->nullable();
            $table->timestamp('LAST_SEEN_AT')->nullable();
            $table->integer('ISLOGIN')->default(0);
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
        Schema::dropIfExists('USER');
    }
}
