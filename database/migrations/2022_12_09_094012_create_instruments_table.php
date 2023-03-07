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
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('title', 500)->index();
            $table->text('description')->nullable();
            $table->text('attachment')->nullable();
            $table->string('parent', 50)->index()->nullable();
            $table->string('category', 20)->index()->nullable();
            $table->string('indicator', 1)->nullable();
            $table->string('action')->nullable();
            $table->boolean('exclude_rate')->nullable();
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
        Schema::dropIfExists('instruments');
    }
};
