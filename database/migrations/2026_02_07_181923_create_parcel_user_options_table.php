<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parcel_user_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_category_id')->constrained()->cascadeOnDelete();
            $table->string('icon')->nullable();
            $table->string('title');
            $table->text('description');
            $table->double('charge_multiplier')->default(1.0);
            $table->string('service_type')->default('custom'); // deliver_now, schedule, truck, custom
            $table->string('tag')->nullable();
            $table->string('tag_color')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcel_user_options');
    }
};
