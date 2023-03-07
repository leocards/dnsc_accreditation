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
        Schema::create('instrument_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('instrumentId');
            $table->unsignedBigInteger('accredId');
            $table->mediumText('comment');
            $table->foreign('userId')->references('id')->on('users');
            $table->foreign('instrumentId')->references('id')->on('instruments');
            $table->foreign('accredId')->references('id')->on('accreditations');
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
        Schema::dropIfExists('instrument_comments');
    }
};
