<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSUSPENDREVOKEAPPEALDOCTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('SUSPEND_REVOKE_APPEAL_DOC', function (Blueprint $table) {
            $table->integer('SR_APPEAL_DOC_ID', true);
            $table->integer('SUSPEND_REVOKE_APPEAL_ID')->nullable()->index('CA_RECORD_DETAILS_ID');
            $table->string('DOC_MIMETYPE', 100)->nullable();
            $table->string('DOC_FILETYPE', 40)->nullable();
            $table->string('DOC_ORIGINAL_NAME', 200)->nullable();
            $table->binary('DOC_BLOB')->nullable();
            $table->decimal('DOC_FILESIZE', 10)->nullable();
            $table->integer('CREATE_BY')->nullable();
            $table->timestamp('CREATE_TIMESTAMP')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('SUSPEND_REVOKE_APPEAL_DOC');
    }
}
