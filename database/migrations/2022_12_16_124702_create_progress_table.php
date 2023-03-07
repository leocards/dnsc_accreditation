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
        Schema::create('progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instrumentId');
            $table->string('parent')->nullable()->index();
            $table->unsignedBigInteger('area');
            $table->unsignedBigInteger('accredlvlId');
            $table->boolean('isComplete')->nullable();
            $table->string('progress')->nullable()->index();
            $table->boolean('isRemoved')->nullable();
            $table->string('rate', 5)->nullable();
            $table->boolean('exclude_rate')->nullable();
            $table->foreign('instrumentId')->references('id')->on('instruments');
            $table->foreign('area')->references('id')->on('instruments');
            $table->foreign('accredlvlId')->references('id')->on('accreditations');
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
        Schema::dropIfExists('progress');
    }
};
