<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDASHBOARDDISTRIBUTORDISPLAYSETTINGTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DASHBOARD_DISTRIBUTOR_DISPLAY_SETTING', function (Blueprint $table) {
            $table->integer('DISPLAY_SETTING_ID', true);
            $table->integer('DASHBOARD_SETTING_ID')->default(0);
            $table->string('SETTING_USER_TYPE', 100)->nullable();
            $table->integer('SETTING_USER_ID')->default(0);
            $table->integer('SETTING_CHART_ID')->default(0);
            $table->integer('SETTING_INDEX')->default(0);
            $table->integer('SETTING_STATUS')->default(0);
            $table->string('DISPLAY_SETTING_STYLE', 50)->nullable();
            $table->timestamp('SETTING_DATE')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DASHBOARD_DISTRIBUTOR_DISPLAY_SETTING');
    }
}
