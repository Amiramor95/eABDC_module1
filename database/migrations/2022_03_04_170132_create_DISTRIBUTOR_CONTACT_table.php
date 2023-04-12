<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORCONTACTTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_CONTACT', function (Blueprint $table) {
            $table->integer('DIST_CONT_ID', true);
            $table->integer('DIST_ID')->unique('DIST_ID');
            $table->string('DIST_TELEPHONE_1', 50)->default('-');
            $table->string('DIST_TELEPHONE_2', 50)->default('-');
            $table->string('DIST_FAX_1', 50)->default('-');
            $table->string('DIST_FAX_2', 50)->default('-');
            $table->string('DIST_EMAIL', 100)->default('-');
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
        Schema::dropIfExists('DISTRIBUTOR_CONTACT');
    }
}
