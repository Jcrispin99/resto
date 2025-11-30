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
        Schema::create('product_product_attributes', function (Blueprint $table) {
            $table->foreignId('product_product_id')->constrained('product_product')->onDelete('cascade');
            $table->foreignId('attribute_value_id')->constrained('product_attribute_values')->onDelete('cascade');
            
            $table->primary(['product_product_id', 'attribute_value_id'], 'pp_attr_val_primary');
            
            // Indexes
            $table->index('product_product_id');
            $table->index('attribute_value_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_product_attributes');
    }
};
