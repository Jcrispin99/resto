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
        Schema::create('product_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained('product_template')->onDelete('cascade');
            $table->string('sku', 50)->unique();
            $table->string('barcode', 100)->unique()->nullable();
            $table->string('variant_name', 200)->nullable()->comment('e.g., "Red - Large"');
            $table->decimal('cost_price', 10, 2)->nullable()->comment('If NULL, use default from template');
            $table->decimal('sale_price', 10, 2)->nullable()->comment('If NULL, use default from template');
            $table->string('image', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default_variant')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('product_template_id');
            $table->index('sku');
            $table->index('barcode');
            $table->index('is_active');
            $table->index('is_default_variant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_product');
    }
};
