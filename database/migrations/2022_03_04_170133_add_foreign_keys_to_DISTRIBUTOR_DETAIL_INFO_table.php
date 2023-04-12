<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDISTRIBUTORDETAILINFOTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('DISTRIBUTOR_DETAIL_INFO', function (Blueprint $table) {
            $table->foreign(['DIST_ID'], 'DISTRIBUTOR_DETAIL_INFO_ibfk_1')->references(['DISTRIBUTOR_ID'])->on('DISTRIBUTOR');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('DISTRIBUTOR_DETAIL_INFO', function (Blueprint $table) {
            $table->dropForeign('DISTRIBUTOR_DETAIL_INFO_ibfk_1');
        });
    }
}
