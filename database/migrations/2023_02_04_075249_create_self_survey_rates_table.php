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
        Schema::create('self_survey_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('surveyId');
            $table->unsignedBigInteger('instrumentId');
            $table->unsignedBigInteger('areaId');
            $table->string('parent')->nullable()->index();
            $table->string('rate')->nullable();
            $table->foreign('surveyId')->references('id')->on('self_surveys');
            $table->foreign('instrumentId')->references('id')->on('instruments');
            $table->foreign('areaId')->references('id')->on('instruments');
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
        Schema::dropIfExists('self_survey_rates');
    }
};
