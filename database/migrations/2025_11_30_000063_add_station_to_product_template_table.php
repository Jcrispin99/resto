<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_template', function (Blueprint $table) {
            $table->foreignId('kitchen_station_id')->nullable()->constrained('kitchen_stations')->onDelete('set null')->after('category_id');
            $table->index('kitchen_station_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_template', function (Blueprint $table) {
            $table->dropForeign(['kitchen_station_id']);
            $table->dropColumn('kitchen_station_id');
        });
    }
};
