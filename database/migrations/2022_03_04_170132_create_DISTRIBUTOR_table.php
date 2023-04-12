<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR', function (Blueprint $table) {
            $table->integer('DISTRIBUTOR_ID', true);
            $table->integer('DIST_REGI_ID')->nullable()->unique('DIST_REGI_ID');
            $table->string('DIST_NAME', 100)->nullable();
            $table->integer('DIST_CODE')->nullable();
            $table->string('DIST_REGI_NUM1', 50)->nullable();
            $table->string('DIST_REGI_NUM2', 50)->nullable();
            $table->date('DIST_DATE_INCORP')->nullable();
            $table->integer('DIST_TYPE_SETUP')->nullable();
            $table->string('DIST_PHONE_NUMBER', 100)->nullable()->default('-');
            $table->string('DIST_PHONE_EXTENSION', 50)->nullable()->default('-');
            $table->string('DIST_MOBILE_NUMBER', 15)->nullable()->default('-');
            $table->string('DIST_FAX_NUMBER', 100)->nullable()->default('-');
            $table->string('DIST_EMAIL', 100)->nullable()->default('-');
            $table->string('DIST_COMPANY_WEBSITE', 100)->nullable()->default('-');
            $table->timestamp('CREATE_TIMESTAMP')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR');
    }
}
