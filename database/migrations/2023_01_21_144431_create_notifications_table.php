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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');//notify to
            $table->unsignedBigInteger('userNotifier')->nullable();//notify by
            $table->unsignedBigInteger('documentId')->nullable();//notify by
            $table->unsignedBigInteger('instrumentId')->nullable();//notify by
            $table->boolean('seen')->nullable()->index();
            $table->boolean('isOwner')->nullable();
            $table->string('action', 50)->nullable();
            $table->string('details', 1000)->nullable();
            $table->foreign('userId')->references('id')->on('users');//notify to
            $table->foreign('userNotifier')->references('id')->on('users');//notify by
            $table->foreign('documentId')->references('id')->on('document_current_versions');//notify by
            $table->foreign('instrumentId')->references('id')->on('instruments');//notify by
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
        Schema::dropIfExists('notifications');
    }
};
