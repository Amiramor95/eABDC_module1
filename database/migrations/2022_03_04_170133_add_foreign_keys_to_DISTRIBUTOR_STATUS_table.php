<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDISTRIBUTORSTATUSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('DISTRIBUTOR_STATUS', function (Blueprint $table) {
            $table->foreign(['DIST_APPROVAL_STATUS'], 'APPR_STATUS')->references(['TS_ID'])->on('TASK_STATUS');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('DISTRIBUTOR_STATUS', function (Blueprint $table) {
            $table->dropForeign('APPR_STATUS');
        });
    }
}
