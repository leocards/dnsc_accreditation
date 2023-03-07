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
        Schema::create('task_assigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('instrumentId');
            $table->unsignedBigInteger('areaId');
            $table->unsignedBigInteger('accredId');
            $table->dateTime('due')->index();
            $table->boolean('complete')->nullable();
            $table->boolean('notified')->nullable()->index();
            $table->boolean('removed')->nullable();
            $table->foreign('userId')->references('id')->on('users');
            $table->foreign('areaId')->references('id')->on('instruments');
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
        Schema::dropIfExists('task_assigns');
    }
};
