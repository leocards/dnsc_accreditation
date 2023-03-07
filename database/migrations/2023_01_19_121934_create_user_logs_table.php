<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('accredlvl');
            $table->unsignedBigInteger('documentId');
            $table->unsignedBigInteger('instrumentId');
            $table->string('details');
            $table->foreign('userId')->references('id')->on('users');
            $table->foreign('accredlvl')->references('id')->on('accreditations');
            $table->foreign('instrumentId')->references('id')->on('instruments');
            $table->foreign('documentId')->references('id')->on('document_versions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userlogs');
    }
};
