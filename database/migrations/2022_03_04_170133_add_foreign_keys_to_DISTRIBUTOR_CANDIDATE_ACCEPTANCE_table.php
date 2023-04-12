<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDISTRIBUTORCANDIDATEACCEPTANCETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('DISTRIBUTOR_CANDIDATE_ACCEPTANCE', function (Blueprint $table) {
            $table->foreign(['DIST_ID'], 'DISTRIBUTOR_ID')->references(['DISTRIBUTOR_ID'])->on('DISTRIBUTOR');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('DISTRIBUTOR_CANDIDATE_ACCEPTANCE', function (Blueprint $table) {
            $table->dropForeign('DISTRIBUTOR_ID');
        });
    }
}
