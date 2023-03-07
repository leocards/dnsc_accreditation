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
        Schema::create('self_surveys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accredlvl');
            $table->string('rate', 5)->nullable();
            $table->foreign('accredlvl')->references('id')->on('accreditations')->onDelete('cascade');
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
        Schema::dropIfExists('self_surveys');
    }
};
