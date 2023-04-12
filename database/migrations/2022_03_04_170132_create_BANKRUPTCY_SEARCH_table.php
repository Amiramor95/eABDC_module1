<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBANKRUPTCYSEARCHTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('BANKRUPTCY_SEARCH', function (Blueprint $table) {
            $table->integer('BANKRUPTCY_SEARCH_ID')->primary();
            $table->string('DIST_ID', 45)->nullable();
            $table->string('TYPE', 45)->nullable();
            $table->string('DEFENDANT_NAME', 45)->nullable();
            $table->string('NEW_IC', 45)->nullable();
            $table->string('OLD_IC', 45)->nullable();
            $table->string('ADJUDICATION_ORDER_DATE', 45)->nullable();
            $table->string('CASE_NO', 45)->nullable();
            $table->string('CREDITOR', 45)->nullable();
            $table->string('SOLICITOR_CODE', 45)->nullable();
            $table->string('SOLICITOR_NAME', 45)->nullable();
            $table->string('SOLICITOR_TEL', 45)->nullable();
            $table->string('SOLICITOR_FAX', 45)->nullable();
            $table->string('REF2', 45)->nullable();
            $table->string('CREATE_TIMESTAMP', 45)->default('CURRENT_TIMESTAMP');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('BANKRUPTCY_SEARCH');
    }
}
