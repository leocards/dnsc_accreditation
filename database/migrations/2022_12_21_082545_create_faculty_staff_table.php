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
        Schema::create('faculty_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('designation');
            $table->unsignedBigInteger('programId')->nullable();
            $table->unsignedBigInteger('instituteId');
            $table->foreign('programId')->references('id')->on('programs');
            $table->foreign('instituteId')->references('id')->on('institutes');
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
        Schema::dropIfExists('faculty_staff');
    }
};
