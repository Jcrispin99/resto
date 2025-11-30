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
        Schema::create('menu_item_stations', function (Blueprint $table) {
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('station_id')->constrained('kitchen_stations')->onDelete('cascade');
            
            $table->primary(['menu_item_id', 'station_id'], 'menu_station_primary');
            
            // Indexes
            $table->index('menu_item_id');
            $table->index('station_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_stations');
    }
};
