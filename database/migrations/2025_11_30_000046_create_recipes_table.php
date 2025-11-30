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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_product_id')->constrained('product_product')->onDelete('restrict');
            $table->decimal('quantity', 10, 3);
            $table->foreignId('unit_id')->constrained()->onDelete('restrict');
            $table->decimal('waste_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('menu_item_id');
            $table->index('product_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
