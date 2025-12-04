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
        Schema::create('productables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('product_product')->onDelete('cascade');
            $table->morphs('productable'); // Crea productable_id + productable_type
            $table->decimal('quantity', 10, 3);
            $table->decimal('price', 10, 2)->comment('Precio unitario usado');
            $table->decimal('discount', 10, 2)->default(0)->comment('Descuento aplicado');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('Monto de impuesto');
            $table->decimal('subtotal', 10, 2)->comment('quantity * price - discount');
            $table->decimal('total', 10, 2)->comment('subtotal + impuestos');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productables');
    }
};
