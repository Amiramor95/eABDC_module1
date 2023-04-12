<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDIVESTMENTDISTAPPROVALTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DIVESTMENT_DIST_APPROVAL', function (Blueprint $table) {
            $table->integer('DIVE_DIST_APPR_ID', true);
            $table->integer('DIVE_ID')->index('DIVE_ID');
            $table->integer('APPR_GROUP_ID');
            $table->integer('APPROVAL_LEVEL_ID');
            $table->string('APPR_REMARK', 500)->nullable();
            $table->integer('TS_ID');
            $table->tinyInteger('APPR_PUBLISH_STATUS');
            $table->integer('CREATE_BY');
            $table->timestamp('CREATE_TIMESTAMP')->useCurrent();
            $table->timestamp('LATEST_UPDATE')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DIVESTMENT_DIST_APPROVAL');
    }
}
