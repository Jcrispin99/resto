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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('product_product')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->morphs('inventoryable'); // Vincula con purchase_orders, sales, etc.
            $table->string('detail')->nullable()->comment('Descripción del movimiento');
            
            // Entrada
            $table->decimal('quantity_in', 10, 3)->default(0);
            $table->decimal('cost_in', 10, 2)->default(0);
            $table->decimal('total_in', 10, 2)->default(0);
            
            // Salida
            $table->decimal('quantity_out', 10, 3)->default(0);
            $table->decimal('cost_out', 10, 2)->default(0);
            $table->decimal('total_out', 10, 2)->default(0);
            
            // Saldo (Balance)
            $table->decimal('quantity_balance', 10, 3)->default(0);
            $table->decimal('cost_balance', 10, 2)->default(0);
            $table->decimal('total_balance', 10, 2)->default(0);
            
            $table->timestamps();

            // Indexes
            $table->index('product_id');
            $table->index('warehouse_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
