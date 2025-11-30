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
        Schema::create('menu_item_modifier_relations', function (Blueprint $table) {
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('modifier_id')->constrained('menu_item_modifiers')->onDelete('cascade');
            $table->integer('order')->default(0);
            
            $table->primary(['menu_item_id', 'modifier_id'], 'menu_modifier_primary');
            
            // Indexes
            $table->index('menu_item_id');
            $table->index('modifier_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_modifier_relations');
    }
};
