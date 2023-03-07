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
        Schema::create('area_assigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('areaId');
            $table->unsignedBigInteger('levelId');
            $table->string('parent', 30)->nullable()->index();
            $table->string('role', 15)->nullable();
            $table->string('action', 15)->nullable();
            $table->foreign('userId')->references('id')->on('users');
            $table->foreign('areaId')->references('id')->on('instruments');
            $table->foreign('levelId')->references('id')->on('accreditations');
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
        Schema::dropIfExists('area_assigns');
    }
};
