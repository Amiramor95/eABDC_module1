<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDISTRIBUTORTYPETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('DISTRIBUTOR_TYPE', function (Blueprint $table) {
            $table->foreign(['DIST_TYPE'], 'DIST_TYPE')->references(['DISTRIBUTOR_TYPE_ID'])->on('DISTRIBUTOR_TYPE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('DISTRIBUTOR_TYPE', function (Blueprint $table) {
            $table->dropForeign('DIST_TYPE');
        });
    }
}
