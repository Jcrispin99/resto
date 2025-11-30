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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_product_id')->constrained('product_product')->onDelete('restrict');
            $table->decimal('quantity', 10, 3);
            $table->decimal('received_quantity', 10, 3)->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('tax_percentage', 5, 2)->default(18.00);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();

            // Indexes
            $table->index('purchase_order_id');
            $table->index('product_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
