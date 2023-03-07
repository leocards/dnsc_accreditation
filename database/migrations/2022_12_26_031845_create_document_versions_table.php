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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('file', 500);
            $table->string('type', 10);
            $table->string('title', 500)->index();
            $table->mediumText('description')->nullable();
            $table->string('parent')->nullable()->index();
            $table->string('review')->nullable();
            $table->boolean('isCurrent')->nullable();
            $table->foreign('userId')->references('id')->on('users');
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
        Schema::dropIfExists('document_versions');
    }
};
