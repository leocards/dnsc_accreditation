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
        Schema::create('attached_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentId');
            $table->unsignedBigInteger('instrumentId');
            $table->string('evidence')->nullable();
            $table->unsignedBigInteger('accredlvl');
            $table->boolean('isRemoved')->nullable();
            $table->foreign('documentId')->references('id')->on('document_current_versions');
            $table->foreign('instrumentId')->references('id')->on('instruments');
            $table->foreign('accredlvl')->references('id')->on('accreditations');
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
        Schema::dropIfExists('attached_documents');
    }
};
