<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORREPRESENTATIVETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_REPRESENTATIVE', function (Blueprint $table) {
            $table->integer('DIST_REPR_ID', true);
            $table->integer('DIST_ID')->index('DIST_ID');
            $table->string('REPR_TYPE', 20)->nullable()->default('-');
            $table->integer('REPR_SALUTATION')->nullable();
            $table->string('REPR_NAME', 100)->nullable()->default('-');
            $table->string('REPR_POSITION', 100)->nullable()->default('-');
            $table->integer('REPR_CITIZEN')->nullable()->comment('1 - Malaysian  2 - Non malaysian');
            $table->string('REPR_NRIC', 100)->nullable()->default('-');
            $table->string('REPR_PASS_NUM', 50)->nullable()->default('-');
            $table->date('REPR_PASS_EXP')->nullable();
            $table->string('REPR_TELEPHONE', 50)->nullable()->default('-');
            $table->string('REPR_PHONE_EXTENSION', 10)->nullable()->default('-');
            $table->string('REPR_MOBILE_NUMBER', 50)->nullable()->default('-');
            $table->string('REPR_EMAIL', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_REPRESENTATIVE');
    }
}
