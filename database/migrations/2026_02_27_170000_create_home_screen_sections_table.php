<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('home_screen_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('title');
            $table->unsignedBigInteger('module_id');
            $table->integer('priority')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['key', 'module_id']);
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_screen_sections');
    }
};
