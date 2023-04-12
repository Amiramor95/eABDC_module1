<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDISTRIBUTORTYPETable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('DISTRIBUTOR_TYPE', function (Blueprint $table) {
            $table->integer('DIST_TYPE_ID', true);
            $table->integer('DIST_ID');
            $table->integer('DIST_TYPE')->nullable()->index('DIST_TYPE');
            $table->integer('ISACTIVE')->nullable()->comment('WILL REFER TASK_STATUS (active,inactive,suspend,terminate)');
            $table->integer('DIST_STATUS');
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
        Schema::dropIfExists('DISTRIBUTOR_TYPE');
    }
}
