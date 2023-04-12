<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAUDITTRAILSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('AUDIT_TRAILS', function (Blueprint $table) {
            $table->bigIncrements('AUDIT_TRAILS_ID');
            $table->string('USER_MODEL')->nullable();
            $table->unsignedBigInteger('USER_ID')->nullable();
            $table->unsignedBigInteger('GROUP_ID')->nullable();
            $table->string('EVENT');
            $table->string('AUDIT_MODEL');
            $table->unsignedBigInteger('AUDIT_ID');
            $table->text('OLD_VALUES')->nullable();
            $table->text('NEW_VALUES')->nullable();
            $table->text('URL')->nullable();
            $table->string('IP_ADDRESS', 45)->nullable();
            $table->string('BROWSER', 1023)->nullable();
            $table->string('TAGS')->nullable();
            $table->timestamp('CREATED_AT')->useCurrent();
            $table->timestamp('UPDATED_AT')->useCurrentOnUpdate()->useCurrent();

            $table->index(['AUDIT_MODEL', 'AUDIT_ID'], 'audit_trails_auditable_type_auditable_id_index');
            $table->index(['USER_ID', 'USER_MODEL']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('AUDIT_TRAILS');
    }
}
