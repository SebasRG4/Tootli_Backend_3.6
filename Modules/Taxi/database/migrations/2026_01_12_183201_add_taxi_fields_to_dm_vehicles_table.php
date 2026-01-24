<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            // New taxi specific fields - check existence first
            if (!Schema::hasColumn('d_m_vehicles', 'brand')) {
                $table->string('brand')->nullable();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'model')) {
                $table->string('model')->nullable();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'plate')) {
                $table->string('plate')->nullable();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'color')) {
                $table->string('color')->nullable();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'year')) {
                $table->integer('year')->nullable();
            }
            if (!Schema::hasColumn('d_m_vehicles', 'seats')) {
                $table->integer('seats')->default(4);
            }
            if (!Schema::hasColumn('d_m_vehicles', 'image')) {
                $table->string('image')->nullable();
            }

            // Make existing delivery-specific fields nullable
            // Note: Schema::hasColumn check not needed for change() but good practice to ensure column exists
            if (Schema::hasColumn('d_m_vehicles', 'type')) {
                $table->string('type')->nullable()->change();
            }
            if (Schema::hasColumn('d_m_vehicles', 'starting_coverage_area')) {
                $table->double('starting_coverage_area', 16, 2)->nullable()->change();
            }
            if (Schema::hasColumn('d_m_vehicles', 'maximum_coverage_area')) {
                $table->double('maximum_coverage_area', 16, 2)->nullable()->change();
            }
            if (Schema::hasColumn('d_m_vehicles', 'extra_charges')) {
                $table->double('extra_charges', 16, 2)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model', 'plate', 'color', 'year', 'seats', 'image']);

            // Revert nullable changes (might fail if data exists with nulls)
            // $table->string('type')->nullable(false)->change();
            // $table->double('starting_coverage_area',16,2)->nullable(false)->change();
            // $table->double('maximum_coverage_area',16,2)->nullable(false)->change();
            // $table->double('extra_charges',16,2)->nullable(false)->change();
        });
    }
};
