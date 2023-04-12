<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEXTENSIONREQUESTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('EXTENSION_REQUEST', function (Blueprint $table) {
            $table->integer('EXTENSION_REQUEST_ID', true);
            $table->integer('DISTRIBUTOR_ID');
            $table->integer('CREATED_BY')->nullable();
            $table->dateTime('SUBMISSION_DATE')->nullable();
            $table->string('EXTENSION_TYPE');
            $table->string('OTHER_EXTENSION_TYPE')->nullable();
            $table->mediumText('JUSTIFICATION');
            $table->date('EXTENSION_END_DATE');
            $table->string('EXTENSION_STATUS_ID', 100)->nullable();
            $table->date('RETURN_DATELINE')->nullable();
            $table->date('EXTENSION_APPROVAL_DATE')->nullable();
            $table->boolean('FIRST_NOTIFICATION')->default(false);
            $table->boolean('SECOND_NOTIFICATION')->default(false);
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
        Schema::dropIfExists('EXTENSION_REQUEST');
    }
}
