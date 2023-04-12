<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDISTRIBUTORAPPROVALDOCUMENTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('DISTRIBUTOR_APPROVAL_DOCUMENT', function (Blueprint $table) {
            $table->foreign(['DIST_APPR_ID'], 'DIST_APPR_ID')->references(['DIST_APPROVAL_ID'])->on('DISTRIBUTOR_APPROVAL')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('DISTRIBUTOR_APPROVAL_DOCUMENT', function (Blueprint $table) {
            $table->dropForeign('DIST_APPR_ID');
        });
    }
}
