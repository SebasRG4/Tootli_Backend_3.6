<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dynamic_section_ecommerces', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('module_id');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
        });

        Schema::create('dynamic_section_ecommerce_stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dynamic_section_ecommerce_id');
            $table->unsignedBigInteger('store_id');
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();

            $table->foreign('dynamic_section_ecommerce_id', 'fk_dse_section')
                ->references('id')->on('dynamic_section_ecommerces')->onDelete('cascade');
            $table->foreign('store_id', 'fk_dse_store')
                ->references('id')->on('stores')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_section_ecommerce_stores');
        Schema::dropIfExists('dynamic_section_ecommerces');
    }
};
