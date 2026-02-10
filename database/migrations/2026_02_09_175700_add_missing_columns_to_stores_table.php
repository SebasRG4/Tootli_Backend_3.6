<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            if (!Schema::hasColumn('stores', 'category_ids')) {
                $table->string('category_ids')->nullable();
            }
            if (!Schema::hasColumn('stores', 'reviews_comments_count')) {
                $table->integer('reviews_comments_count')->default(0);
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
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'category_ids')) {
                $table->dropColumn('category_ids');
            }
            if (Schema::hasColumn('stores', 'reviews_comments_count')) {
                $table->dropColumn('reviews_comments_count');
            }
        });
    }
}
