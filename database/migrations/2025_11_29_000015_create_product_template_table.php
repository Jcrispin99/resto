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
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('restrict');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->enum('type', ['ingredient', 'consumable', 'finished_product', 'service'])->default('ingredient');
            $table->boolean('is_stockable')->default(true);
            $table->boolean('is_perishable')->default(false);
            $table->integer('shelf_life_days')->nullable();
            $table->decimal('min_stock', 10, 3)->default(0);
            $table->decimal('max_stock', 10, 3)->nullable();
            $table->decimal('reorder_point', 10, 3)->nullable();
            $table->decimal('default_cost_price', 10, 2)->default(0);
            $table->decimal('default_sale_price', 10, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(18.00);
            $table->string('image', 255)->nullable();
            $table->boolean('has_variants')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('category_id');
            $table->index('unit_id');
            $table->index('type');
            $table->index('is_stockable');
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
