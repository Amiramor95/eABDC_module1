<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateREVOCATIONLICENSETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('REVOCATION_LICENSE', function (Blueprint $table) {
            $table->integer('REVOCATION_LICENSE_ID', true);
            $table->integer('SUSPEND_REVOKE_ID')->nullable()->unique('SUSPEND_REVOKE_ID');
            $table->integer('LICENSE_TYPE')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('REVOCATION_LICENSE');
    }
}
