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
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_product_id')->constrained('product_product')->onDelete('cascade');
            $table->decimal('quantity', 10, 3)->default(0);
            $table->decimal('reserved_quantity', 10, 3)->default(0);
            $table->decimal('last_purchase_price', 10, 2)->default(0);
            $table->decimal('average_cost', 10, 2)->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Indexes
            $table->unique(['warehouse_id', 'product_product_id']);
            $table->index('warehouse_id');
            $table->index('product_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
