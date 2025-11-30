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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_product_id')->constrained('product_product')->onDelete('cascade');
            $table->enum('movement_type', ['purchase', 'sale', 'transfer', 'adjustment', 'production', 'waste', 'return']);
            $table->string('reference_type', 50)->nullable()->comment('purchase_order, sale_order, production_order, etc.');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->decimal('previous_stock', 10, 3);
            $table->decimal('new_stock', 10, 3);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('warehouse_id');
            $table->index('product_product_id');
            $table->index('movement_type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
