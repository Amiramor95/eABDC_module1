<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORDETAILINFOTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_DETAIL_INFO', function (Blueprint $table) {
            $table->integer('DIST_INFO_ID', true);
            $table->integer('DIST_ID')->index('DIST_ID');
            $table->string('DIST_PAID_UP_CAPITAL', 100)->nullable();
            $table->integer('DIST_TYPE_STRUCTURE')->nullable()->comment('1 - Single-tier  2 - Multi-tier');
            $table->tinyInteger('DIST_MARKETING_APPROACH')->nullable()->comment('1 - Direct  2 - Nominee');
            $table->integer('DIST_NUM_DIST_POINT')->nullable();
            $table->integer('DIST_NUM_CONSULTANT')->nullable();
            $table->string('DIST_INSURANCE', 100)->nullable()->default('-');
            $table->date('DIST_EXPIRED_DATE')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_DETAIL_INFO');
    }
}
