<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORREGISTRATIONTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_REGISTRATION', function (Blueprint $table) {
            $table->integer('DIST_REGI_ID', true);
            $table->integer('DIST_USER_ID')->nullable()->unique('FK');
            $table->string('DIST_NAME', 100)->nullable();
            $table->string('DIST_REGI_NUM1', 50)->nullable();
            $table->string('DIST_REGI_NUM2', 50)->nullable();
            $table->date('DIST_DATE_INCORP')->nullable();
            $table->integer('DIST_TYPE_SETUP')->nullable();
            $table->string('DIST_ADDR_1', 100)->nullable();
            $table->string('DIST_ADDR_2', 100)->nullable();
            $table->string('DIST_ADDR_3', 100)->nullable();
            $table->integer('DIST_POSTAL')->nullable();
            $table->string('DIST_TELEPHONE', 50)->nullable();
            $table->string('DIST_FAX', 50)->nullable();
            $table->string('DIST_EMAIL', 100)->nullable();
            $table->integer('DIST_TYPE_APPL')->nullable();
            $table->integer('DIST_INFO_CAPITAL')->nullable();
            $table->integer('DIST_INFO_TIER')->nullable();
            $table->integer('DIST_INFO_MARKETING')->nullable();
            $table->integer('DIST_INFO_POINT')->nullable();
            $table->integer('DIST_INFO_CONS')->nullable();
            $table->integer('DIST_AR_SALUTATION')->nullable();
            $table->string('DIST_AR_FNAME', 100)->nullable();
            $table->string('DIST_AR_MNAME', 100)->nullable();
            $table->string('DIST_AR_LNAME', 100)->nullable();
            $table->string('DIST_AR_POSITION', 50)->nullable();
            $table->integer('DIST_AR_CITIZEN')->nullable();
            $table->string('DIST_AR_NRIC', 12)->nullable();
            $table->string('DIST_AR_PASS_NUM', 50)->nullable();
            $table->date('DIST_AR_PASS_EXP')->nullable();
            $table->string('DIST_AR_TELEPHONE', 50)->nullable();
            $table->string('DIST_AR_EMAIL', 100)->nullable();
            $table->integer('DIST_AAR_SALUTATION')->nullable();
            $table->string('DIST_AAR_FNAME', 100)->nullable();
            $table->string('DIST_AAR_MNAME', 100)->nullable();
            $table->string('DIST_AAR_LNAME', 100)->nullable();
            $table->string('DIST_AAR_POSITION', 50)->nullable();
            $table->integer('DIST_AAR_CITIZEN')->nullable();
            $table->string('DIST_AAR_NRIC', 12)->nullable();
            $table->string('DIST_AAR_PASS_NUM', 50)->nullable();
            $table->date('DIST_AAR_PASS_EXP')->nullable();
            $table->string('DIST_AAR_TELEPHONE', 50)->nullable();
            $table->string('DIST_AAR_EMAIL', 100)->nullable();
            $table->integer('DIST_CP_TYPE')->nullable();
            $table->integer('DIST_CP_SALUTATION')->nullable();
            $table->string('DIST_CP_FNAME', 100)->nullable();
            $table->string('DIST_CP_MNAME', 100)->nullable();
            $table->string('DIST_CP_LNAME', 100)->nullable();
            $table->string('DIST_CP_POSITION', 50)->nullable();
            $table->integer('DIST_CP_CITIZEN')->nullable();
            $table->string('DIST_CP_NRIC', 12)->nullable();
            $table->string('DIST_CP_PASS_NUM', 50)->nullable();
            $table->date('DIST_CP_PASS_EXP')->nullable();
            $table->string('DIST_CP_TELEPHONE', 50)->nullable();
            $table->string('DIST_CP_FAX', 50)->nullable();
            $table->string('DIST_CP_EMAIL', 100)->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_REGISTRATION');
    }
}
