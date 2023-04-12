<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSUBSEQUENTEXTENSIONREQUESTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SUBSEQUENT_EXTENSION_REQUEST', function (Blueprint $table) {
            $table->integer('SUBSEQUENT_EXTENSION_REQUEST_ID', true);
            $table->integer('EXTENSION_REQUEST_ID')->nullable();
            $table->integer('DISTRIBUTOR_ID');
            $table->integer('CREATED_BY')->nullable();
            $table->dateTime('SUBMISSION_DATE')->nullable();
            $table->string('EXTENSION_TYPE', 100)->nullable();
            $table->string('OTHER_EXTENSION_TYPE', 100)->nullable();
            $table->mediumText('JUSTIFICATION')->nullable();
            $table->date('EXTENSION_END_DATE')->nullable();
            $table->integer('TS_ID')->nullable();
            $table->date('RETURN_DATELINE')->nullable();
            $table->date('EXTENSION_APPROVAL_DATE')->nullable();
            $table->boolean('FIRST_NOTIFICATION')->default(false);
            $table->boolean('SECOND_NOTIFICATION')->default(false);
            $table->boolean('IS_NOTIFIED')->nullable()->default(false);
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
        Schema::dropIfExists('SUBSEQUENT_EXTENSION_REQUEST');
    }
}
