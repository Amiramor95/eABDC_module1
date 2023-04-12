<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORAPPROVALDOCUMENTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_APPROVAL_DOCUMENT', function (Blueprint $table) {
            $table->integer('DIST_APPR_DOC_ID', true);
            $table->integer('DIST_APPR_ID')->nullable()->index('DIST_APPR_ID_idx');
            $table->integer('DIST_ID');
            $table->integer('DIST_TYPE_ID');
            $table->integer('REQUIRED_DOC_ID');
            $table->string('DOCU_REMARK', 500)->nullable();
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
        Schema::dropIfExists('DISTRIBUTOR_APPROVAL_DOCUMENT');
    }
}
