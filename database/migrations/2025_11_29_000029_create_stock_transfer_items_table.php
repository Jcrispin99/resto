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
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('product_product_id')->constrained('product_product')->onDelete('restrict');
            $table->decimal('quantity', 10, 3);
            $table->decimal('received_quantity', 10, 3)->default(0);
            $table->timestamps();

            // Indexes
            $table->index('transfer_id');
            $table->index('product_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
