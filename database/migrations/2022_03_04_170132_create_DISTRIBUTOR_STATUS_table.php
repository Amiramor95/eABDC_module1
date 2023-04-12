<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORSTATUSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_STATUS', function (Blueprint $table) {
            $table->integer('DIST_STAT_ID', true);
            $table->integer('DIST_ID')->unique('DIST_ID');
            $table->tinyInteger('DIST_PUBLISH_STATUS')->comment('0 - draft   1 - submit');
            $table->tinyInteger('DIST_VALID_STATUS')->default(0)->comment('0 - not active 1 - active');
            $table->integer('DIST_APPROVAL_STATUS')->nullable()->index('APPR_STATUS');
            $table->timestamp('CREATE_TIMESTAMP')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DISTRIBUTOR_STATUS');
    }
}
