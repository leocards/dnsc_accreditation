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
        Schema::create('accreditations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_self_survey');
            $table->dateTime('date_actual_survey');
            $table->boolean('restrict')->nullable();
            $table->string('status')->nullable();
            $table->boolean('selfsurvey')->index()->nullable();
            $table->unsignedBigInteger('instrumentId');
            $table->unsignedBigInteger('programId');
            $table->tinyInteger('survey')->nullable();
            $table->boolean('verified')->nullable();
            $table->foreign('instrumentId')->references('id')->on('instruments');
            $table->foreign('programId')->references('id')->on('programs');
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
        Schema::dropIfExists('accreditations');
    }
};
