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
        Schema::create('product_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('restrict');
            $table->foreignId('menu_category_id')->nullable()->constrained('product_categories')->onDelete('set null')->comment('Categoría para mostrar en POS');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('internal_reference', 50)->nullable();
            $table->string('barcode', 100)->nullable();
            $table->enum('product_type', ['consumable', 'storable', 'service', 'combo'])->default('storable')->comment('combo = paquete de productos');
            $table->boolean('can_be_sold')->default(false)->comment('Si true, aparece en POS como producto vendible');
            $table->boolean('can_be_purchased')->default(true);
            $table->boolean('can_be_stocked')->default(true);
            $table->decimal('sale_price', 10, 2)->default(0)->comment('Precio base de venta');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('category_id');
            $table->index('menu_category_id');
            $table->index('unit_id');
            $table->index('product_type');
            $table->index('can_be_sold');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_template');
    }
};
