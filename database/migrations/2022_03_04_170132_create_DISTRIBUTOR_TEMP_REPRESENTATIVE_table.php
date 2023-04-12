<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTEMPREPRESENTATIVETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TEMP_REPRESENTATIVE', function (Blueprint $table) {
            $table->integer('DIST_TEMP_REPR_ID', true);
            $table->integer('DIST_TEMP_ID')->index('DIST_TEMP_ID');
            $table->string('REPR_TYPE', 20)->nullable();
            $table->integer('REPR_SALUTATION');
            $table->string('REPR_NAME', 100)->nullable();
            $table->string('REPR_MNAME', 100)->nullable();
            $table->string('REPR_LNAME', 100)->nullable();
            $table->string('REPR_POSITION', 100)->nullable();
            $table->integer('REPR_CITIZEN')->nullable();
            $table->string('REPR_NRIC', 12)->nullable()->default('-');
            $table->string('REPR_PASS_NUM', 50)->nullable();
            $table->date('REPR_PASS_EXP')->nullable();
            $table->string('REPR_MOBILE_NUMBER', 45)->nullable()->default('-');
            $table->string('REPR_TELEPHONE', 50)->nullable()->default('-');
            $table->string('REPR_PHONE_EXTENSION', 45)->nullable()->default('-');
            $table->string('REPR_FAX', 50)->nullable()->default('-');
            $table->string('REPR_EMAIL', 100)->nullable()->default('-');
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
        Schema::dropIfExists('DISTRIBUTOR_TEMP_REPRESENTATIVE');
    }
}
